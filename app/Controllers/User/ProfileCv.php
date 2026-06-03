<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Libraries\AdminImpersonation;
use App\Libraries\AiPublicationParser;
use App\Libraries\CvAiFileStorage;
use App\Libraries\CvProfile;
use App\Libraries\CvPublicationDedupe;
use App\Libraries\PublicationCatalog;
use App\Libraries\PublicationAuthorSearch;
use App\Libraries\PublicationDisplay;
use App\Libraries\PublicationIdentity;
use App\Libraries\PublicationResearchFields;
use App\Libraries\ResearchRecordSsoBridge;
use App\Libraries\RrPublicationType;
use App\Libraries\OrcidPublicRecord;
use App\Libraries\OrcidCvImport;
use App\Libraries\ResearchRecordCvPull;
use App\Libraries\ResearchRecordCvSyncClient;
use App\Libraries\ResearchRecordCvSyncMerge;
use App\Libraries\StaffImageUpload;
use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;
use App\Models\UserModel;
use Config\AiCv;
use Config\ResearchApi;
use Config\ResearchRecordSync as ResearchRecordSyncConfig;

/**
 * จัดการ CV แบบ researchRecord: cv_sections + cv_entries (ผูก personnel + email)
 */
class ProfileCv extends BaseController
{
    private function sessionEmail(): string
    {
        return CvProfile::normalizeEmail((string) session()->get('admin_email'));
    }

    private function resolveOwnedPersonnel(): ?array
    {
        $email = $this->sessionEmail();
        if ($email === '') {
            return null;
        }

        $personnelModel = new PersonnelModel();
        $row = $personnelModel->findByUserEmail($email);
        if ($row !== null) {
            return $row;
        }

        return $personnelModel->groupStart()
            ->where('user_email', null)
            ->orWhere('user_email', '')
            ->groupEnd()
            ->where('email', $email)
            ->first();
    }

    /** URL หลังบันทึกชื่อ/คำนำหน้า: CV (แท็บ identity) ถ้ามี personnel ไม่งั้นโปรไฟล์ */
    private function identityFormRedirectUrl(): string
    {
        if ($this->resolveOwnedPersonnel() !== null) {
            return base_url('dashboard/profile/cv?tab=identity');
        }

        return base_url('dashboard/profile');
    }

    /**
     * @return int|\CodeIgniter\HTTP\RedirectResponse
     */
    private function personnelIdOrRedirect()
    {
        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากรที่ผูกกับอีเมลบัญชีของคุณ กรุณาติดต่อเจ้าหน้าที่เพื่อเชื่อมข้อมูลในระบบ');
        }

        return (int) ($person['id'] ?? 0);
    }

    private function loadCvSectionsWithEntries(int $personnelId): array
    {
        $cvSectionModel = new CvSectionModel();
        $cvEntryModel   = new CvEntryModel();
        if (!$cvSectionModel->db->tableExists('cv_sections')) {
            return [];
        }
        if (! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return [];
        }

        ResearchRecordCvSyncMerge::finalizeCvSectionsForPerson($personnelId);

        $sections = $cvSectionModel->where('personnel_id', $personnelId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($sections as &$section) {
            $entries = $cvEntryModel->where('section_id', (int) $section['id'])
                ->orderedForCvDisplay()
                ->findAll();
            foreach ($entries as &$entry) {
                $entry['metadata_array'] = CvEntryModel::decodeMetadata($entry['metadata'] ?? null);
            }
            unset($entry);
            $section['entries'] = $entries;
        }
        unset($section);

        return PublicationDisplay::enrichSections($personnelId, $sections);
    }

    public function index()
    {
        $email = $this->sessionEmail();
        if ($email === '') {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person !== null) {
            $withUser = (new PersonnelModel())->findWithUser((int) ($person['id'] ?? 0));
            if ($withUser !== null) {
                $person = $withUser;
            }
        }
        $publicCvUrl = null;
        if ($person !== null) {
            $cvEmail = !empty($person['user_email'])
                ? CvProfile::normalizeEmail((string) $person['user_email'])
                : CvProfile::normalizeEmail((string) ($person['email'] ?? $email));
            if ($cvEmail !== '') {
                $publicCvUrl = base_url('personnel-cv/' . rawurlencode($cvEmail));
            }
        }

        $uid = (int) session()->get('admin_id');
        $accountUser = $uid > 0 ? (new UserModel())->find($uid) : null;
        $researchSyncConfigured = config(\Config\ResearchApi::class)->syncConfigured();

        return view('user/profile/index', [
            'page_title'    => 'โปรไฟล์และประวัติ',
            'person'        => $person,
            'public_cv_url' => $publicCvUrl,
            'session_email' => $email,
            'account_user'  => $accountUser,
            'research_sync_configured' => $researchSyncConfigured,
        ]);
    }

    /**
     * POST — คำนำหน้า: หลักที่ personnel (ถ้ามีแถวและคอลัมน์) มิฉะนั้นเก็บที่ user.title — ชื่อแยกซิงก์ลง user เสมอ
     */
    public function saveAccountIdentity()
    {
        $email = $this->sessionEmail();
        if ($email === '') {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $userId = (int) session()->get('admin_id');
        if ($userId <= 0) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $userModel = new UserModel();
        $user      = $userModel->find($userId);
        if (! $user) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบบัญชีผู้ใช้');
        }

        if (CvProfile::normalizeEmail((string) ($user['email'] ?? '')) !== $email) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่สามารถแก้ไขบัญชีนี้ได้');
        }

        $person = $this->resolveOwnedPersonnel();
        $hasPersonnel = $person !== null && (int) ($person['id'] ?? 0) > 0;

        $title   = trim((string) $this->request->getPost('title'));
        $titleEn = trim((string) $this->request->getPost('academic_title_en'));
        if (! CvProfile::isAllowedUserTitle($title)) {
            return redirect()->to($this->identityFormRedirectUrl())->withInput()->with('error', 'คำนำหน้าชื่อไม่ตรงกับรายการมาตรฐาน กรุณาเลือกจากรายการ');
        }
        if ($hasPersonnel && $titleEn !== '' && ! array_key_exists($titleEn, CvProfile::academicTitleOptionsEn())) {
            return redirect()->to($this->identityFormRedirectUrl())->withInput()->with('error', 'คำนำหน้าชื่อ (English) ไม่ตรงกับรายการมาตรฐาน');
        }
        $tf    = trim((string) $this->request->getPost('tf_name'));
        $tl    = trim((string) $this->request->getPost('tl_name'));
        $gf    = trim((string) $this->request->getPost('gf_name'));
        $gl    = trim((string) $this->request->getPost('gl_name'));

        $max = 255;
        foreach (['คำนำหน้า' => $title, 'ชื่อ (ไทย)' => $tf, 'นามสกุล (ไทย)' => $tl, 'ชื่อ (อังกฤษ)' => $gf, 'นามสกุล (อังกฤษ)' => $gl] as $label => $val) {
            if (mb_strlen($val) > $max) {
                return redirect()->to($this->identityFormRedirectUrl())->withInput()->with('error', "{$label} ยาวเกิน {$max} ตัวอักษร");
            }
        }

        $hasThai  = $tf !== '' || $tl !== '';
        $hasRoman = $gf !== '' || $gl !== '';
        if (! $hasThai && ! $hasRoman) {
            return redirect()->to($this->identityFormRedirectUrl())->withInput()->with('error', 'กรุณากรอกชื่อ-นามสกุลอย่างน้อยหนึ่งภาษา (ไทยหรืออังกฤษ)');
        }

        $nameTh = trim($tf . ' ' . $tl);
        $nameEn = trim($gf . ' ' . $gl);

        $personnelModel = new PersonnelModel();
        $storeTitleOnPersonnel = $hasPersonnel && $personnelModel->db->fieldExists('academic_title', 'personnel');

        if ($hasPersonnel) {
            $personnelId = (int) ($person['id'] ?? 0);
            $pUpdate = [
                'name'    => $nameTh !== '' ? $nameTh : ($person['name'] ?? ''),
                'name_en' => $nameEn !== '' ? $nameEn : null,
            ];
            if ($storeTitleOnPersonnel) {
                $pUpdate['academic_title'] = $title !== '' ? $title : null;
            }
            if ($personnelModel->db->fieldExists('academic_title_en', 'personnel')) {
                $pUpdate['academic_title_en'] = $titleEn !== '' ? $titleEn : null;
            }
            $personnelModel->skipValidation(true)->update($personnelId, $pUpdate);
        }

        // user.title เมื่อไม่มีคอลัมน์/ไม่เก็บใน personnel — หลีกเลี่ยงซ้ำซ้อนเมื่อเก็บที่ personnel แล้ว
        $userTitle = $storeTitleOnPersonnel ? null : ($title !== '' ? $title : null);

        $userUpdate = [
            'title'   => $userTitle,
            'tf_name' => $tf !== '' ? $tf : null,
            'tl_name' => $tl !== '' ? $tl : null,
            'gf_name' => $gf !== '' ? $gf : null,
            'gl_name' => $gl !== '' ? $gl : null,
        ];
        $ok = $userModel->skipValidation(true)->update($userId, $userUpdate);

        if (! $ok) {
            return redirect()->to($this->identityFormRedirectUrl())->withInput()->with('error', 'บันทึกไม่สำเร็จ กรุณาลองอีกครั้ง');
        }

        $fresh = $userModel->find($userId);
        if ($fresh) {
            if ($hasPersonnel) {
                $with = (new PersonnelModel())->findWithUser((int) $person['id']);
                if ($with !== null) {
                    session()->set('admin_name', PersonnelModel::resolvePublicDisplayNameTh($with));
                } else {
                    $t = trim((string) ($fresh['title'] ?? ''));
                    $base = $userModel->getFullName($fresh);
                    session()->set('admin_name', $t !== '' ? trim($t . ' ' . $base) : $base);
                }
            } else {
                $t = trim((string) ($fresh['title'] ?? ''));
                $base = $userModel->getFullName($fresh);
                session()->set('admin_name', $t !== '' ? trim($t . ' ' . $base) : $base);
            }
        }

        return redirect()->to($this->identityFormRedirectUrl())->with('success', $storeTitleOnPersonnel
            ? 'บันทึกแล้ว — คำนำหน้าหลักในข้อมูลบุคลากร ชื่อ-นามสกุลซิงก์บัญชีผู้ใช้'
            : 'บันทึกแล้ว — คำนำหน้าอยู่ที่บัญชีผู้ใช้ (ยังไม่มีการเก็บใน personnel หรือยังไม่เชื่อมบุคลากร)');
    }

    public function cv()
    {
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        $email = $this->sessionEmail();
        if ($email === '') {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากรที่ผูกกับอีเมลบัญชีของคุณ กรุณาติดต่อเจ้าหน้าที่เพื่อเชื่อมข้อมูลในระบบ');
        }

        $personnelId = (int) $person['id'];
        $withUser = (new PersonnelModel())->findWithUser($personnelId);
        if ($withUser !== null) {
            $person = $withUser;
        }

        $uid = (int) session()->get('admin_id');
        $accountUser = $uid > 0 ? (new UserModel())->find($uid) : null;

        $cvSectionModel = new CvSectionModel();
        if (!$cvSectionModel->db->tableExists('cv_sections')) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ระบบ CV ยังไม่พร้อม — รัน php spark migrate (ต้องมีตาราง cv_sections)');
        }
        if (! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)');
        }

        $researchApi            = config(ResearchApi::class);
        $researchSyncConfigured = $researchApi->syncConfigured();
        $syncCfg                = config(ResearchRecordSyncConfig::class);
        $rrSyncNotice           = null;
        $rrLastPullFormatted    = null;

        if ($researchSyncConfigured) {
            $lastPull = ResearchRecordCvPull::lastSuccessfulRrPullAt($personnelId);
            if ($lastPull !== null) {
                $rrLastPullFormatted = $lastPull->format('d/m/Y H:i');
            }
        }

        $canonical = ResearchRecordCvPull::canonicalEmailForPerson($person);

        if ($researchSyncConfigured && $this->request->getGet('rr_sync') === '1') {
            $pubPull = ResearchRecordCvPull::pullPublicationsOnly($personnelId, $canonical, 'rr_return_sync');
            if ($pubPull['success'] ?? false) {
                $rrLastPullFormatted = (new \DateTimeImmutable())->format('d/m/Y H:i');
                $rrSyncNotice        = [
                    'type'   => 'success',
                    'text'   => 'กลับจากระบบ กบศ — ' . ($pubPull['message'] ?? 'ดึงผลงานแล้ว'),
                    'detail' => 'รายการในหัวข้อผลงานอัปเดตจาก กบศ แล้ว',
                ];
            } else {
                $rrSyncNotice = [
                    'type'   => 'warning',
                    'text'   => 'ดึงผลงานจาก กบศ ไม่สำเร็จ',
                    'detail' => $pubPull['message'] ?? '',
                ];
            }
        } elseif ($researchSyncConfigured) {
            $trigger = ResearchRecordCvPull::shouldAutoPull($personnelId, $syncCfg->autoPullMaxAgeDays);
            if ($trigger !== false) {
                $pullRes = ResearchRecordCvPull::run($personnelId, $canonical, $trigger);
                if ($pullRes['success']) {
                    $rrLastPullFormatted = (new \DateTimeImmutable())->format('d/m/Y H:i');
                    $rrSyncNotice        = [
                        'type' => 'success',
                        'text' => $trigger === ResearchRecordCvPull::TRIGGER_AUTO_EMPTY
                            ? 'ซิงค์จาก กบศ แบบเสริมอัตโนมัติแล้ว (ยังไม่มีข้อมูลในระบบ — รักษาข้อมูลที่กรอกใน ฐานข้อมูลคณะเป็นหลัก)'
                            : 'ซิงค์จาก กบศ แบบเสริมอัตโนมัติแล้ว (ครั้งดึงล่าสุดเกิน ' . $syncCfg->autoPullMaxAgeDays . ' วัน — ข้อมูลที่กรอกใน ฐานข้อมูลคณะไม่ถูกแทนที่ด้วยกบศ)',
                        'detail' => $pullRes['message'] ?? '',
                    ];
                } else {
                    $rrSyncNotice = [
                        'type'   => 'warning',
                        'text'   => 'ดึงจาก กบศ อัตโนมัติไม่สำเร็จ',
                        'detail' => $pullRes['message'] ?? '',
                    ];
                }
            }
        }

        $cvSections = $this->loadCvSectionsWithEntries($personnelId);
        $personnelModel = new PersonnelModel();
        helper('cv');

        $tabRaw = strtolower((string) $this->request->getGet('tab'));
        $cvEditTabs = ['identity', 'narrative', 'photo', 'orcid', 'sections'];
        $cvEditActiveTab = in_array($tabRaw, $cvEditTabs, true) ? $tabRaw : 'narrative';

        $aiCvCfg = config(AiCv::class);
        $rrSsoCfg = config(\Config\ResearchRecordSso::class);
        $cvPublicationSectionId = 0;
        foreach ($cvSections as $sec) {
            if (! is_array($sec) || ! ResearchRecordCvSyncMerge::isPublicationCvSection($sec)) {
                continue;
            }
            $cvPublicationSectionId = (int) ($sec['id'] ?? 0);
            if ($cvPublicationSectionId > 0) {
                break;
            }
        }
        $ownerEmail = ResearchRecordCvPull::canonicalEmailForPerson($person);
        $ownerName = PersonnelModel::resolvePublicDisplayNameTh($person);

        $rrSsoReady = $rrSsoCfg->enabled
            && $rrSsoCfg->baseUrl !== ''
            && $rrSsoCfg->sharedSecret !== ''
            && $rrSsoCfg->sharedSecret !== 'SHARED_SECRET_PLACEHOLDER';
        $cvReturnForRr = $this->buildCvReturnUrl($cvPublicationSectionId);
        $rrPubCreateDirect = ResearchRecordSsoBridge::absoluteRrPathWithNsReturn('publications/create', $cvReturnForRr);
        $rrPubCreateSso = $rrSsoReady && $ownerEmail !== ''
            ? ResearchRecordSsoBridge::signedEntryUrl(
                $ownerEmail,
                $ownerName,
                ResearchRecordSsoBridge::rrEntryPath('publications/create'),
                $cvReturnForRr
            )
            : null;

        return view('user/profile/cv_manage', [
            'page_title'                 => 'จัดการ CV',
            'person'                     => $person,
            'account_user'               => $accountUser,
            'cv_sections'                => $cvSections,
            'cv_owner_email'             => $ownerEmail,
            'cv_owner_name'              => $ownerName,
            'cv_photo_supported'         => $personnelModel->db->fieldExists('cv_profile_image', 'personnel'),
            'research_sync_configured'   => $researchSyncConfigured,
            'rr_sync_notice'             => $rrSyncNotice,
            'rr_last_pull_at'            => $rrLastPullFormatted,
            'rr_auto_pull_max_age_days'  => $researchSyncConfigured ? $syncCfg->autoPullMaxAgeDays : null,
            'cv_edit_active_tab'         => $cvEditActiveTab,
            'ai_cv_publication_enabled'     => $aiCvCfg->isReady(),
            'cv_publication_section_id'     => $cvPublicationSectionId,
            'research_record_sso_ready'     => $rrSsoReady,
            'rr_publication_create_direct'  => $rrPubCreateDirect,
            'rr_publication_create_sso'     => $rrPubCreateSso,
            'cv_ui_build'                   => 'publication-rr-v3',
        ]);
    }

    /**
     * Redirect ไป RR เพิ่มผลงาน (SSO) — กลับมาหน้า CV หลังบันทึก
     */
    public function goRrPublicationCreate()
    {
        return $this->redirectToResearchRecordPublication('create');
    }

    /**
     * Redirect ไป RR แก้ผลงาน (SSO)
     */
    public function goRrPublicationEdit(int $rrId)
    {
        return $this->redirectToResearchRecordPublication('edit', $rrId);
    }

    /**
     * Redirect ไป RR จัดการผลงาน (SSO) — ใช้จากแดชบอร์ด
     */
    public function goRrPublicationManage()
    {
        return $this->redirectToResearchRecordPublication('manage');
    }

    /**
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function redirectToResearchRecordPublication(string $action, int $rrId = 0)
    {
        if (AdminImpersonation::isActive()) {
            return redirect()->to(base_url('dashboard/profile/cv?tab=sections'))
                ->with('error', 'ไม่สามารถไปยังระบบภายนอกระหว่าง Login As ได้');
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $email = ResearchRecordCvPull::canonicalEmailForPerson($person);
        if ($email === '') {
            return redirect()->to(base_url('dashboard/profile/cv?tab=sections'))
                ->with('error', 'ไม่พบอีเมลสำหรับเข้า กบศ — กรุณาติดต่อเจ้าหน้าที่');
        }

        $sectionId = (int) $this->request->getGet('section_id');
        $returnAfter = $this->buildCvReturnUrl($sectionId);

        $entryRedirect = match ($action) {
            'edit'   => ResearchRecordSsoBridge::rrEntryPath('publications/edit/' . max(1, $rrId)),
            'manage' => ResearchRecordSsoBridge::rrEntryPath('publications/manage'),
            default  => ResearchRecordSsoBridge::rrEntryPath('publications/create'),
        };

        $name = PersonnelModel::resolvePublicDisplayNameTh($person);
        $url  = ResearchRecordSsoBridge::signedEntryUrl($email, $name, $entryRedirect, $returnAfter);
        if ($url === null) {
            return redirect()->to(base_url('dashboard/profile/cv?tab=sections'))
                ->with('error', 'Research Record SSO ยังไม่พร้อมใช้งาน');
        }

        return redirect()->to($url);
    }

    private function buildCvReturnUrl(int $sectionId = 0): string
    {
        $params = ['tab' => 'sections'];
        if ($sectionId > 0) {
            $params['open_section'] = (string) $sectionId;
        }

        return base_url('dashboard/profile/cv?' . http_build_query($params));
    }


    public function searchPersonnelNames()
    {
        if ($this->sessionEmail() === '') {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        }
        if ($this->resolveOwnedPersonnel() === null) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลบุคลากร']);
        }

        $name = trim((string) $this->request->getGet('name'));
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        if (mb_strlen($name) < 2) {
            return $this->response->setJSON(['success' => true, 'results' => []]);
        }

        return $this->response->setJSON([
            'success' => true,
            'results' => PublicationAuthorSearch::searchByName($name, $limit),
        ]);
    }

    public function searchPersonnelEmail()
    {
        if ($this->sessionEmail() === '') {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        }
        if ($this->resolveOwnedPersonnel() === null) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลบุคลากร']);
        }

        $email = trim((string) $this->request->getGet('email'));
        if (mb_strlen($email) < 3) {
            return $this->response->setJSON(['success' => true, 'found' => false]);
        }

        $result = PublicationAuthorSearch::resolveByEmail($email);

        return $this->response->setJSON([
            'success' => true,
            'found'   => $result !== null,
            'result'  => $result,
        ]);
    }

    /**
     * POST — บันทึกข้อความแนะนำ / ความเชี่ยวชาญ (personnel) สำหรับหน้า CV สาธารณะ
     */
    public function saveCvNarrative()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('dashboard/profile/cv'));
        }

        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $rules = [
            'bio'       => 'permit_empty|string|max_length[20000]',
            'expertise' => 'permit_empty|string|max_length[5000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $personnelModel = new PersonnelModel();
        $personnelModel->update($personnelId, [
            'bio'       => (string) $this->request->getPost('bio'),
            'expertise' => (string) $this->request->getPost('expertise'),
        ]);

        return redirect()->back()->with('success', 'บันทึกการแนะนำข้อมูลและความเชี่ยวชาญแล้ว');
    }


    /**
     * POST — อัปโหลดรูปประกอบ CV สาธารณะ (ไม่แก้ user.profile_image)
     */
    public function saveCvPhoto()
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $personnelModel = new PersonnelModel();
        if (!$personnelModel->db->fieldExists('cv_profile_image', 'personnel')) {
            return redirect()->back()->with('error', 'ระบบยังไม่รองรับรูป CV — รัน php spark migrate');
        }

        $person = $personnelModel->find($personnelId);
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $imagePath = StaffImageUpload::handleUpload($this->request->getFile('image'));
        if ($imagePath === null) {
            return redirect()->back()->with('error', 'ไม่มีไฟล์หรือไฟล์ไม่ถูกต้อง (รองรับ JPG, PNG, GIF, WebP ไม่เกิน 20MB)');
        }

        $old = trim((string) ($person['cv_profile_image'] ?? ''));
        if ($old !== '') {
            StaffImageUpload::deleteStaffImageFile($old);
        }

        $personnelModel->update($personnelId, ['cv_profile_image' => $imagePath]);

        return redirect()->back()->with('success', 'อัปโหลดรูปประกอบ CV เรียบร้อยแล้ว');
    }

    /**
     * POST — ลบรูปประกอบ CV
     */
    public function removeCvPhoto()
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $personnelModel = new PersonnelModel();
        if (!$personnelModel->db->fieldExists('cv_profile_image', 'personnel')) {
            return redirect()->back()->with('error', 'ระบบยังไม่รองรับรูป CV — รัน php spark migrate');
        }

        $person = $personnelModel->find($personnelId);
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $old = trim((string) ($person['cv_profile_image'] ?? ''));
        if ($old !== '') {
            StaffImageUpload::deleteStaffImageFile($old);
        }

        $personnelModel->update($personnelId, ['cv_profile_image' => null]);

        return redirect()->back()->with('success', 'ลบรูปประกอบ CV แล้ว');
    }

    /**
     * POST — เพิ่มหัวข้อ (แบบ researchRecord)
     */
    public function saveCvSection()
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '' || mb_strlen($title) > 255) {
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อหัวข้อ (ไม่เกิน 255 ตัวอักษร)');
        }

        $type = (string) ($this->request->getPost('type') ?? 'custom');
        $allowedTypes = ['education', 'work', 'experience', 'funding', 'research', 'articles', 'courses', 'service', 'custom'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'custom';
        }

        $cvSectionModel = new CvSectionModel();
        $sortOrder        = $cvSectionModel->nextSortOrder($personnelId);

        $cvSectionModel->insert([
            'personnel_id'        => $personnelId,
            'type'                => $type,
            'title'               => $title,
            'description'         => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'          => $sortOrder,
            'is_default'          => $type === 'custom' ? 0 : 0,
            'visible_on_public'   => 1,
        ]);

        return redirect()->back()->with('success', 'เพิ่มหัวข้อใหม่เรียบร้อยแล้ว');
    }

    public function reorderCvSections()
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $order = json_decode((string) $this->request->getPost('order'), true);
            if (empty($order) || !is_array($order)) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลลำดับ']);
            }

            $cvSectionModel = new CvSectionModel();
            foreach ($order as $item) {
                $sectionId = (int) ($item['id'] ?? 0);
                $sortOrder = (int) ($item['order'] ?? 0);
                $section   = $cvSectionModel->find($sectionId);
                if ($section && (int) $section['personnel_id'] === $personnelId) {
                    $cvSectionModel->update($sectionId, ['sort_order' => $sortOrder]);
                }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'บันทึกลำดับเรียบร้อยแล้ว']);
        } catch (\Throwable $e) {
            log_message('error', 'reorderCvSections: ' . $e->getMessage());

            return $this->response->setJSON(['success' => false, 'message' => 'Error']);
        }
    }

    public function toggleCvSectionPublic(int $sectionId)
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find($sectionId);
        if (!$section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบหัวข้อ']);
        }

        $new = empty($section['visible_on_public']) ? 1 : 0;
        $cvSectionModel->update($sectionId, ['visible_on_public' => $new]);

        if ($new === 0 && CvEntryModel::isTablePresent($cvSectionModel->db)) {
            $cvEntryModel = new CvEntryModel();
            $cvEntryModel->where('section_id', $sectionId)->update(null, ['visible_on_public' => 0]);
        }

        return $this->response->setJSON([
            'success'             => true,
            'visible_on_public'   => $new,
            'entries_set_private' => $new === 0,
            'message'             => $new
                ? 'แสดงในหน้าสาธารณะแล้ว'
                : 'ซ่อนหัวข้อจากหน้าสาธารณะ และปิดการแสดงสาธารณะของรายการย่อยทั้งหมดในหัวข้อนี้แล้ว',
        ]);
    }

    public function toggleCvEntryPublic(int $entryId)
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        if (! CvEntryModel::isTablePresent()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)']);
        }

        $cvEntryModel   = new CvEntryModel();
        $entry          = $cvEntryModel->find($entryId);
        if (!$entry) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบรายการ']);
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find((int) $entry['section_id']);
        if (!$section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์แก้ไขรายการนี้']);
        }

        $new = empty($entry['visible_on_public']) ? 1 : 0;
        $cvEntryModel->update($entryId, ['visible_on_public' => $new]);

        return $this->response->setJSON([
            'success'           => true,
            'visible_on_public' => $new,
            'message'           => $new ? 'แสดงรายการนี้ในหน้าสาธารณะแล้ว' : 'ซ่อนรายการนี้จากหน้าสาธารณะแล้ว',
        ]);
    }

    public function deleteCvSection(int $sectionId)
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find($sectionId);
        if (!$section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบหัวข้อ']);
        }

        if (ResearchRecordCvSyncMerge::isProtectedDefaultCvSection($section)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => ResearchRecordCvSyncMerge::protectedSectionDeleteMessage($section),
            ]);
        }

        if (CvEntryModel::isTablePresent($cvSectionModel->db)) {
            (new CvEntryModel())->where('section_id', $sectionId)->delete();
        }
        $cvSectionModel->delete($sectionId);

        return $this->response->setJSON(['success' => true, 'message' => 'ลบหัวข้อเรียบร้อยแล้ว']);
    }

    public function reorderCvEntries()
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            if (! CvEntryModel::isTablePresent()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)']);
            }

            $sectionId = (int) $this->request->getPost('section_id');
            $order     = json_decode((string) $this->request->getPost('order'), true);
            if ($sectionId <= 0 || empty($order) || !is_array($order)) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลลำดับ']);
            }

            $cvSectionModel = new CvSectionModel();
            $section        = $cvSectionModel->find($sectionId);
            if (!$section || (int) $section['personnel_id'] !== $personnelId) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
            }

            $cvEntryModel = new CvEntryModel();
            foreach ($order as $item) {
                $entryId   = (int) ($item['id'] ?? 0);
                $sortOrder = (int) ($item['order'] ?? 0);
                $entry     = $cvEntryModel->find($entryId);
                if ($entry && (int) $entry['section_id'] === $sectionId) {
                    $cvEntryModel->update($entryId, ['sort_order' => $sortOrder]);
                }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'บันทึกลำดับเรียบร้อยแล้ว']);
        } catch (\Throwable $e) {
            log_message('error', 'reorderCvEntries: ' . $e->getMessage());

            return $this->response->setJSON(['success' => false, 'message' => 'Error']);
        }
    }

    public function saveCvEntry()
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $userDataCheck = $this->sessionEmail();
        if ($userDataCheck === '') {
            return redirect()->to(base_url('admin/login'));
        }

        $sectionId = (int) $this->request->getPost('section_id');
        $entryId   = (int) $this->request->getPost('entry_id');

        if ($sectionId <= 0) {
            return $this->ajaxOrRedirectError('ไม่พบหัวข้อที่ต้องการบันทึก');
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find($sectionId);
        if (!$section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->ajaxOrRedirectError('ไม่สามารถเข้าถึงหัวข้อได้');
        }

        if (! CvEntryModel::isTablePresent()) {
            return $this->ajaxOrRedirectError('ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)');
        }

        $title = trim((string) $this->request->getPost('entry_title'));
        if ($title === '' || mb_strlen($title) > 500) {
            return $this->ajaxOrRedirectError('กรุณากรอกชื่อรายการ');
        }

        $isPubSection = $this->isPublicationSection($section);
        $fromCvPublicationPage = $this->request->getPost('cv_publication_page') === '1';
        if ($isPubSection && ! $fromCvPublicationPage) {
            $rrId = 0;
            if ($entryId > 0) {
                $row = (new CvEntryModel())->find($entryId);
                if (is_array($row)) {
                    $meta = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
                    $rrId = (int) ($meta['rr_publication_id'] ?? 0);
                }
            }
            if ($rrId > 0) {
                return redirect()->to(base_url('dashboard/profile/cv/rr-publication/edit/' . $rrId . '?section_id=' . $sectionId))
                    ->with('info', 'แก้ไขเนื้อหาผลงานที่ระบบ กบศ');
            }

            return redirect()->to(base_url('dashboard/profile/cv/rr-publication/create?section_id=' . $sectionId))
                ->with('info', 'บันทึกผลงานที่ระบบ กบศ');
        }

        $cvEntryModel = new CvEntryModel();
        $existing     = null;
        if ($entryId > 0) {
            $existing = $cvEntryModel->find($entryId);
            if (!$existing) {
                return $this->ajaxOrRedirectError('ไม่พบรายการที่ต้องการแก้ไข');
            }
            if ((int) $existing['section_id'] !== $sectionId) {
                return $this->ajaxOrRedirectError('หัวข้อไม่ตรงกับรายการ');
            }
            $entrySection = $cvSectionModel->find($existing['section_id']);
            if (!$entrySection || (int) $entrySection['personnel_id'] !== $personnelId) {
                return $this->ajaxOrRedirectError('ไม่สามารถแก้ไขรายการนี้ได้');
            }
        }

        $url    = trim((string) $this->request->getPost('entry_url'));
        $extra  = trim((string) $this->request->getPost('extra_info'));
        $amount = trim((string) $this->request->getPost('funding_amount'));

        $meta = $existing ? CvEntryModel::decodeMetadata($existing['metadata'] ?? null) : [];
        if ($url !== '') {
            $meta['url'] = mb_substr($url, 0, 2048);
        } else {
            unset($meta['url']);
        }
        if ($extra !== '') {
            $meta['extra_info'] = $extra;
        } else {
            unset($meta['extra_info']);
        }
        if ($amount !== '') {
            $meta['amount'] = $amount;
        } else {
            unset($meta['amount']);
        }

        if ($isPubSection) {
            $meta = PublicationResearchFields::mergeResearchMetadataFromPost($this->request->getPost(), $meta);

            $ptype = trim((string) $this->request->getPost('publication_type'));
            if ($ptype === '') {
                unset($meta['rr_publication_type']);
            } elseif (RrPublicationType::isValidPublicationTypeCode($ptype)) {
                $meta['rr_publication_type'] = mb_substr($ptype, 0, 80);
            }

            $doiRaw = trim((string) $this->request->getPost('entry_doi'));
            $doiNorm = PublicationIdentity::normalizeDoi($doiRaw);
            if ($doiNorm !== '') {
                $meta['doi'] = $doiNorm;
            } else {
                unset($meta['doi']);
            }

            $postRr = trim((string) $this->request->getPost('publication_rr_id'));
            if ($postRr !== '' && ctype_digit($postRr) && (int) $postRr > 0) {
                $meta['rr_publication_id'] = (int) $postRr;
            } else {
                unset($meta['rr_publication_id']);
            }

            $symEk = PublicationIdentity::syncExternalKeyFromMetadata($meta);
            if ($symEk !== '') {
                $meta['sync_external_key'] = $symEk;
            } else {
                unset($meta['sync_external_key']);
            }

            $srcPost = trim((string) $this->request->getPost('entry_metadata_source'));
            if ($srcPost === 'ai_assistant') {
                $meta['source'] = 'ai_assistant';
            }
        } else {
            unset($meta['rr_publication_type']);
            unset($meta['doi'], $meta['rr_publication_id'], $meta['sync_external_key']);
            foreach (PublicationResearchFields::BIBLIO_KEYS as $bibKey) {
                unset($meta[$bibKey]);
            }
            unset($meta['publication_year_be'], $meta['publication_month'], $meta['publication_authors']);
            if (($meta['source'] ?? '') === 'ai_assistant') {
                unset($meta['source']);
            }
        }

        if ($entryId <= 0 && $isPubSection) {
            $dupId = CvPublicationDedupe::findDuplicateEntryId($personnelId, $meta, 0);
            if ($dupId !== null) {
                $entryId   = $dupId;
                $existing  = $cvEntryModel->find($dupId);
                $oldMeta   = $existing ? CvEntryModel::decodeMetadata($existing['metadata'] ?? null) : [];
                if (empty($meta['rr_publication_id']) && ! empty($oldMeta['rr_publication_id'])) {
                    $meta['rr_publication_id'] = (int) $oldMeta['rr_publication_id'];
                }
                if (empty($meta['source']) && ! empty($oldMeta['source'])) {
                    $meta['source'] = (string) $oldMeta['source'];
                }
                $symEk = PublicationIdentity::syncExternalKeyFromMetadata($meta);
                if ($symEk !== '') {
                    $meta['sync_external_key'] = $symEk;
                } else {
                    unset($meta['sync_external_key']);
                }
            }
        }

        $postSort = (int) ($this->request->getPost('entry_sort_order') ?? 0);

        $startDate = $this->request->getPost('start_date') ?: null;
        $description = trim((string) $this->request->getPost('entry_description')) ?: null;
        if ($isPubSection) {
            $yearBeRaw = trim((string) $this->request->getPost('publication_year_be'));
            if ($yearBeRaw !== '' && ctype_digit($yearBeRaw)) {
                $yearCe = PublicationResearchFields::normalizeYearToCe((int) $yearBeRaw);
                if ($yearCe !== null) {
                    $month = (int) ($meta['publication_month'] ?? 0);
                    $month = ($month >= 1 && $month <= 12) ? $month : 1;
                    $startDate = sprintf('%04d-%02d-01', $yearCe, $month);
                }
            }
            $abstract = trim((string) ($meta['abstract'] ?? ''));
            if ($description === null && $abstract !== '') {
                $description = $abstract;
            }
            $refUrl = trim((string) ($meta['ref_url'] ?? ''));
            if ($refUrl !== '' && empty($meta['url'])) {
                $meta['url'] = mb_substr($refUrl, 0, 2048);
            }
        }
        $meta = ResearchRecordCvSyncMerge::stampNewScienceContentMetadata($meta);
        $metadataJson = $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;

        $entryData = [
            'section_id'        => $sectionId,
            'title'             => $title,
            'organization'      => trim((string) $this->request->getPost('organization')) ?: null,
            'location'          => trim((string) $this->request->getPost('location')) ?: null,
            'start_date'        => $startDate,
            'end_date'          => $this->request->getPost('end_date') ?: null,
            'is_current'        => $this->request->getPost('is_current') ? 1 : 0,
            'description'       => $description,
            'metadata'          => $metadataJson,
            'visible_on_public' => $this->request->getPost('visible_on_public') ? 1 : 0,
        ];

        if ($entryId > 0) {
            $entryData['sort_order'] = $postSort > 0 ? $postSort : (int) ($existing['sort_order'] ?? 0);
            if ($existing !== null && (int) ($existing['section_id'] ?? 0) !== $sectionId) {
                $entryData['section_id'] = $sectionId;
            }
            $cvEntryModel->update($entryId, $entryData);
            $savedId = $entryId;
        } else {
            $entryData['sort_order'] = $postSort > 0 ? $postSort : $cvEntryModel->nextSortOrder($sectionId);
            $cvEntryModel->insert($entryData);
            $savedId = (int) $cvEntryModel->getInsertID();
        }

        if ($this->request->isAJAX()) {
            $savedEntry = $cvEntryModel->find($savedId);
            if (is_string($savedEntry['metadata'] ?? null)) {
                $savedEntry['metadata_array'] = CvEntryModel::decodeMetadata($savedEntry['metadata']);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'บันทึกข้อมูลสำเร็จ',
                'entry'   => $savedEntry,
            ]);
        }

        if ($this->request->getPost('cv_publication_page')) {
            $open = 'dashboard/profile/cv?tab=sections&open_section=' . $sectionId;

            return redirect()->to(base_url($open))->with('success', 'บันทึกผลงานสำเร็จ');
        }

        return redirect()->back()->with('success', 'บันทึกข้อมูลสำเร็จ');
    }

    /**
     * @param array<string,mixed> $section
     */
    private function isPublicationSection(array $section): bool
    {
        return ResearchRecordCvSyncMerge::isPublicationCvSection($section);
    }

    /**
     * @param array<string,mixed> $entry
     *
     * @return array<string,mixed>
     */
    private function hydrateCvEntryForEditor(array $entry, int $personnelId): array
    {
        $entry['metadata_array'] = CvEntryModel::decodeMetadata($entry['metadata'] ?? null);
        $entry['entry_url']      = (string) ($entry['metadata_array']['url'] ?? $entry['metadata_array']['legacy_url'] ?? '');
        $entry['publication_type'] = (string) ($entry['metadata_array']['rr_publication_type'] ?? '');
        $entry['entry_doi']        = (string) ($entry['metadata_array']['doi'] ?? '');
        $rrPid                     = (int) ($entry['metadata_array']['rr_publication_id'] ?? 0);
        $entry['publication_rr_id'] = $rrPid > 0 ? (string) $rrPid : '';
        $yearBe = (int) ($entry['metadata_array']['publication_year_be'] ?? 0);
        if ($yearBe <= 0 && ! empty($entry['start_date']) && preg_match('/^(\d{4})/', (string) $entry['start_date'], $ym)) {
            $yearBe = (int) PublicationResearchFields::yearBeFromCe((int) $ym[1]);
        }
        $entry['publication_year_be'] = $yearBe > 0 ? (string) $yearBe : '';
        $entry['publication_month'] = (string) ($entry['metadata_array']['publication_month'] ?? '');
        foreach (PublicationResearchFields::BIBLIO_KEYS as $bibKey) {
            $entry[$bibKey] = (string) ($entry['metadata_array'][$bibKey] ?? '');
        }
        if (PublicationCatalog::isReady()) {
            $syncKey = PublicationIdentity::syncExternalKeyFromMetadata($entry['metadata_array']);
            if ($syncKey !== '') {
                $catalogRow = (new \App\Models\PublicationModel())->where('sync_external_key', $syncKey)->first();
                if (is_array($catalogRow)) {
                    $biblio = PublicationResearchFields::decodeBibliographicFromPublicationRow($catalogRow);
                    foreach (PublicationResearchFields::BIBLIO_KEYS as $bibKey) {
                        if (($entry[$bibKey] ?? '') === '' && ! empty($biblio[$bibKey])) {
                            $entry[$bibKey] = (string) $biblio[$bibKey];
                        }
                    }
                    if ($entry['publication_month'] === '' && ! empty($biblio['publication_month'])) {
                        $entry['publication_month'] = (string) $biblio['publication_month'];
                    }
                    if ($entry['publication_year_be'] === '' && ! empty($catalogRow['publication_year'])) {
                        $entry['publication_year_be'] = (string) PublicationResearchFields::yearBeFromCe((int) $catalogRow['publication_year']);
                    }
                }
            }
        }
        $authors = PublicationCatalog::lookupContributorsForMetadata($entry['metadata_array']);
        $entry['publication_authors'] = $authors;
        foreach (['start_date', 'end_date'] as $df) {
            if (! empty($entry[$df]) && strlen((string) $entry[$df]) > 10) {
                $entry[$df] = substr((string) $entry[$df], 0, 10);
            }
        }

        return $entry;
    }

    private function ajaxOrRedirectError(string $msg)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => $msg]);
        }

        return redirect()->back()->with('error', $msg);
    }

    public function getCvEntry(?int $entryId = null)
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        if (!$entryId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Entry ID required']);
        }

        if (! CvEntryModel::isTablePresent()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)']);
        }

        $cvEntryModel = new CvEntryModel();
        $entry        = $cvEntryModel->find($entryId);
        if (!$entry) {
            return $this->response->setJSON(['success' => false, 'message' => 'Entry not found']);
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find($entry['section_id']);
        if (!$section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $entry = $this->hydrateCvEntryForEditor($entry, $personnelId);

        return $this->response->setJSON(['success' => true, 'entry' => $entry]);
    }

    public function deleteCvEntry(?int $entryId = null)
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }

            return $personnelId;
        }

        if (!$entryId) {
            return $this->ajaxOrRedirectError('ไม่พบรายการที่ต้องการลบ');
        }

        if (! CvEntryModel::isTablePresent()) {
            return $this->ajaxOrRedirectError('ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)');
        }

        $cvEntryModel = new CvEntryModel();
        $entry        = $cvEntryModel->find($entryId);
        if (!$entry) {
            return $this->ajaxOrRedirectError('ไม่พบรายการที่ต้องการลบ');
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find($entry['section_id']);
        if (!$section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->ajaxOrRedirectError('ไม่สามารถลบรายการนี้ได้');
        }

        $cvEntryModel->delete($entryId);

        if (in_array((string) ($section['type'] ?? ''), ['research', 'articles'], true)) {
            PublicationCatalog::detachAfterCvEntryDelete($personnelId, $section, $entry);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'ลบรายการเรียบร้อยแล้ว']);
        }

        return redirect()->back()->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    /**
     * ลบผลงานทั้งรายการในระบบวิจัย (เฉพาะผู้บันทึก) — RR ลบให้ทุกผู้แต่ง (cascade)
     */
    public function deleteRrPublication(?int $entryId = null)
    {
        return $this->rrPublicationDeleteAction($entryId, 'delete');
    }

    /**
     * นำชื่อของผู้ใช้ออกจากผลงาน (ผู้ถูก tag ที่ไม่ใช่ผู้บันทึก) — ข้อมูลผลงานยังอยู่
     */
    public function untagRrPublication(?int $entryId = null)
    {
        return $this->rrPublicationDeleteAction($entryId, 'untag');
    }

    /**
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    private function rrPublicationDeleteAction(?int $entryId, string $mode)
    {
        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        }
        if (AdminImpersonation::isActive()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถดำเนินการระหว่าง Login As ได้']);
        }
        if (! $entryId || ! CvEntryModel::isTablePresent()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบรายการที่ต้องการ']);
        }

        $cvEntryModel = new CvEntryModel();
        $entry        = $cvEntryModel->find($entryId);
        if (! $entry) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบรายการที่ต้องการ']);
        }

        $cvSectionModel = new CvSectionModel();
        $section        = $cvSectionModel->find($entry['section_id']);
        if (! $section || (int) $section['personnel_id'] !== $personnelId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถดำเนินการกับรายการนี้ได้']);
        }

        $meta = CvEntryModel::decodeMetadata($entry['metadata'] ?? null);
        $rrId = (int) ($meta['rr_publication_id'] ?? 0);
        if ($rrId <= 0) {
            // รายการที่ยังไม่ผูกกับระบบวิจัย — ลบเฉพาะรายการในหน้านี้
            $cvEntryModel->delete($entryId);

            return $this->response->setJSON(['success' => true, 'message' => 'ลบรายการเรียบร้อยแล้ว']);
        }

        $person = $this->resolveOwnedPersonnel();
        $email  = $person !== null ? ResearchRecordCvPull::canonicalEmailForPerson($person) : '';
        if ($email === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบอีเมลสำหรับตรวจสอบสิทธิ์']);
        }

        $res = $mode === 'delete'
            ? ResearchRecordCvSyncClient::deletePublication($email, $rrId)
            : ResearchRecordCvSyncClient::untagAuthor($email, $rrId);

        if (! ($res['success'] ?? false)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => (string) ($res['message'] ?? 'ดำเนินการไม่สำเร็จ'),
            ]);
        }

        $cvEntryModel->delete($entryId);

        return $this->response->setJSON([
            'success' => true,
            'message' => $mode === 'delete' ? 'ลบผลงานออกจากระบบเรียบร้อยแล้ว' : 'นำชื่อของคุณออกจากผลงานเรียบร้อยแล้ว',
        ]);
    }

    /**
     * POST — บันทึกเฉพาะ ORCID iD (ไม่ดึงข้อมูลจาก ORCID) — ว่าง = ล้างค่า
     */
    public function saveOrcidId()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('dashboard/profile/cv?tab=orcid'));
        }

        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $personnelModel = new PersonnelModel();
        if (! $personnelModel->db->fieldExists('orcid_id', 'personnel')) {
            return redirect()->to(base_url('dashboard/profile/cv?tab=orcid'))->with('error', 'ระบบยังไม่รองรับคอลัมน์ ORCID — รัน migrate');
        }

        $orcidRaw = trim((string) $this->request->getPost('orcid_id'));
        if ($orcidRaw === '') {
            $personnelModel->update($personnelId, ['orcid_id' => null]);

            return redirect()->to(base_url('dashboard/profile/cv?tab=orcid'))->with('success', 'ล้างเลข ORCID แล้ว');
        }

        if (! OrcidPublicRecord::isValidId($orcidRaw)) {
            return redirect()->to(base_url('dashboard/profile/cv?tab=orcid'))->withInput()->with('error', 'รูปแบบ ORCID iD ไม่ถูกต้อง (เช่น 0000-0002-1825-0097)');
        }

        $orcidId = OrcidPublicRecord::normalizeId($orcidRaw);
        $personnelModel->update($personnelId, ['orcid_id' => $orcidId]);

        return redirect()->to(base_url('dashboard/profile/cv?tab=orcid'))->with('success', 'บันทึกเลข ORCID แล้ว');
    }

    /**
     * POST (AJAX) — ดึงจาก ORCID Public API แล้วบันทึกลง cv_sections / cv_entries
     *
     * พารามิเตอร์: orcid_id, scopes (optional) = comma-separated: education,employment,works — default ทั้งหมด
     */
    public function importOrcidCv()
    {
        if ($this->sessionEmail() === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลบุคลากรที่ผูกกับบัญชีของคุณ']);
        }

        $personnelId = (int) ($person['id'] ?? 0);
        if ($personnelId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'ข้อมูลบุคลากรไม่สมบูรณ์']);
        }

        $cvSectionModel = new CvSectionModel();
        if (!$cvSectionModel->db->tableExists('cv_sections')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ระบบ CV ยังไม่พร้อม — รัน php spark migrate (ต้องมีตาราง cv_sections)']);
        }
        if (! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)']);
        }

        $orcidRaw = trim((string) $this->request->getPost('orcid_id'));
        if ($orcidRaw === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณากรอก ORCID iD']);
        }

        if (!OrcidPublicRecord::isValidId($orcidRaw)) {
            return $this->response->setJSON(['success' => false, 'message' => 'รูปแบบ ORCID iD ไม่ถูกต้อง (เช่น 0000-0002-1825-0097)']);
        }

        $scopes = OrcidCvImport::normalizeScopes(
            $this->parseOrcidImportScopes((string) $this->request->getPost('scopes'))
        );
        if ($scopes === []) {
            return $this->response->setJSON(['success' => false, 'message' => 'เลือกอย่างน้อยหนึ่งประเภทการนำเข้า']);
        }

        $result = OrcidCvImport::import($personnelId, $orcidRaw, $scopes);

        return $this->response->setJSON($result);
    }

    /**
     * @return list<string>
     */
    private function parseOrcidImportScopes(string $scopesRaw): array
    {
        $allowed = ['education', 'employment', 'works'];
        if (trim($scopesRaw) === '') {
            return $allowed;
        }

        $parts = array_map('trim', explode(',', $scopesRaw));
        $parts = array_map('strtolower', $parts);
        $out   = array_values(array_intersect($allowed, $parts));

        return $out;
    }



}
