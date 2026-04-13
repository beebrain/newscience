<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Libraries\CvProfile;
use App\Libraries\RrPublicationType;
use App\Libraries\OrcidPublicRecord;
use App\Libraries\ResearchRecordCvPull;
use App\Libraries\StaffImageUpload;
use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;
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

        return $sections;
    }

    public function index()
    {
        $email = $this->sessionEmail();
        if ($email === '') {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $person = $this->resolveOwnedPersonnel();
        $publicCvUrl = null;
        if ($person !== null) {
            $cvEmail = !empty($person['user_email'])
                ? CvProfile::normalizeEmail((string) $person['user_email'])
                : CvProfile::normalizeEmail((string) ($person['email'] ?? $email));
            if ($cvEmail !== '') {
                $publicCvUrl = base_url('personnel-cv/' . rawurlencode($cvEmail));
            }
        }

        return view('user/profile/index', [
            'page_title'    => 'โปรไฟล์และประวัติ',
            'person'        => $person,
            'public_cv_url' => $publicCvUrl,
            'session_email' => $email,
        ]);
    }

    public function cv()
    {
        $email = $this->sessionEmail();
        if ($email === '') {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากรที่ผูกกับอีเมลบัญชีของคุณ กรุณาติดต่อเจ้าหน้าที่เพื่อเชื่อมข้อมูลในระบบ');
        }

        $personnelId = (int) $person['id'];
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

        if ($researchSyncConfigured) {
            $trigger = ResearchRecordCvPull::shouldAutoPull($personnelId, $syncCfg->autoPullMaxAgeDays);
            if ($trigger !== false) {
                $canonical = ResearchRecordCvPull::canonicalEmailForPerson($person);
                $pullRes     = ResearchRecordCvPull::run($personnelId, $canonical, $trigger);
                if ($pullRes['success']) {
                    $rrLastPullFormatted = (new \DateTimeImmutable())->format('d/m/Y H:i');
                    $rrSyncNotice        = [
                        'type' => 'success',
                        'text' => $trigger === ResearchRecordCvPull::TRIGGER_AUTO_EMPTY
                            ? 'ดึง CV จาก Research Record ลง newScience อัตโนมัติแล้ว (ยังไม่มีข้อมูลในระบบ)'
                            : 'อัปเดต CV จาก Research Record อัตโนมัติแล้ว (ครั้งดึงล่าสุดเกิน ' . $syncCfg->autoPullMaxAgeDays . ' วัน)',
                        'detail' => $pullRes['message'] ?? '',
                    ];
                } else {
                    $rrSyncNotice = [
                        'type'   => 'warning',
                        'text'   => 'ดึงจาก Research Record อัตโนมัติไม่สำเร็จ',
                        'detail' => $pullRes['message'] ?? '',
                    ];
                }
            }
        }

        $cvSections = $this->loadCvSectionsWithEntries($personnelId);
        $personnelModel = new PersonnelModel();
        helper('cv');

        $tabRaw = strtolower((string) $this->request->getGet('tab'));
        $cvEditTabs = ['narrative', 'photo', 'orcid', 'sections'];
        $cvEditActiveTab = in_array($tabRaw, $cvEditTabs, true) ? $tabRaw : 'narrative';

        return view('user/profile/cv_manage', [
            'page_title'                 => 'จัดการ CV',
            'person'                     => $person,
            'cv_sections'                => $cvSections,
            'cv_photo_supported'         => $personnelModel->db->fieldExists('cv_profile_image', 'personnel'),
            'research_sync_configured'   => $researchSyncConfigured,
            'rr_sync_notice'             => $rrSyncNotice,
            'rr_last_pull_at'            => $rrLastPullFormatted,
            'rr_auto_pull_max_age_days'  => $researchSyncConfigured ? $syncCfg->autoPullMaxAgeDays : null,
            'cv_edit_active_tab'         => $cvEditActiveTab,
        ]);
    }

    /**
     * POST — บันทึกข้อความแนะนำ / การศึกษา (สรุป) / ความเชี่ยวชาญ (personnel) สำหรับหน้า CV สาธารณะ
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
            'education' => 'permit_empty|string|max_length[20000]',
            'expertise' => 'permit_empty|string|max_length[5000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $personnelModel = new PersonnelModel();
        $personnelModel->update($personnelId, [
            'bio'       => (string) $this->request->getPost('bio'),
            'education' => (string) $this->request->getPost('education'),
            'expertise' => (string) $this->request->getPost('expertise'),
        ]);

        return redirect()->back()->with('success', 'บันทึกการแนะนำข้อมูลและความเชี่ยวชาญแล้ว');
    }

    /**
     * POST — ดึง CV + ผลงานจาก Research Record ลง newScience (manual)
     */
    public function syncFromResearchRecord()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('dashboard/profile/cv'));
        }

        $personnelId = $this->personnelIdOrRedirect();
        if ($personnelId instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $personnelId;
        }

        $personnelModel = new PersonnelModel();
        $person          = $personnelModel->find($personnelId);
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $researchApi = config(ResearchApi::class);
        if (! $researchApi->syncConfigured()) {
            return redirect()->back()->with('error', 'ยังไม่ได้ตั้งค่า Research API ใน .env (RESEARCH_API_BASE_URL, RESEARCH_API_KEY)');
        }

        $canonical = ResearchRecordCvPull::canonicalEmailForPerson($person);
        $result    = ResearchRecordCvPull::run($personnelId, $canonical, ResearchRecordCvPull::TRIGGER_MANUAL);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message'] ?? 'ดึงจาก Research Record เรียบร้อย');
        }

        return redirect()->back()->with('error', $result['message'] ?? 'ดึงจาก Research Record ไม่สำเร็จ');
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

        $secType = (string) ($section['type'] ?? '');
        if (in_array($secType, ['research', 'articles'], true)) {
            $ptype = trim((string) $this->request->getPost('publication_type'));
            if ($ptype === '') {
                unset($meta['rr_publication_type']);
            } elseif (RrPublicationType::isValidPublicationTypeCode($ptype)) {
                $meta['rr_publication_type'] = mb_substr($ptype, 0, 80);
            }
        } else {
            unset($meta['rr_publication_type']);
        }

        $metadataJson = $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;

        $postSort = (int) ($this->request->getPost('entry_sort_order') ?? 0);

        $entryData = [
            'section_id'        => $sectionId,
            'title'             => $title,
            'organization'      => trim((string) $this->request->getPost('organization')) ?: null,
            'location'          => trim((string) $this->request->getPost('location')) ?: null,
            'start_date'        => $this->request->getPost('start_date') ?: null,
            'end_date'          => $this->request->getPost('end_date') ?: null,
            'is_current'        => $this->request->getPost('is_current') ? 1 : 0,
            'description'       => trim((string) $this->request->getPost('entry_description')) ?: null,
            'metadata'          => $metadataJson,
            'visible_on_public' => $this->request->getPost('visible_on_public') ? 1 : 0,
        ];

        if ($entryId > 0) {
            $entryData['sort_order'] = $postSort > 0 ? $postSort : (int) ($existing['sort_order'] ?? 0);
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

        return redirect()->back()->with('success', 'บันทึกข้อมูลสำเร็จ');
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

        $entry['metadata_array'] = CvEntryModel::decodeMetadata($entry['metadata'] ?? null);
        $entry['entry_url']      = (string) ($entry['metadata_array']['url'] ?? $entry['metadata_array']['legacy_url'] ?? '');
        $entry['publication_type'] = (string) ($entry['metadata_array']['rr_publication_type'] ?? '');
        foreach (['start_date', 'end_date'] as $df) {
            if (!empty($entry[$df]) && strlen((string) $entry[$df]) > 10) {
                $entry[$df] = substr((string) $entry[$df], 0, 10);
            }
        }

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

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'ลบรายการเรียบร้อยแล้ว']);
        }

        return redirect()->back()->with('success', 'ลบรายการเรียบร้อยแล้ว');
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

        $scopes = $this->parseOrcidImportScopes((string) $this->request->getPost('scopes'));
        if ($scopes === []) {
            return $this->response->setJSON(['success' => false, 'message' => 'เลือกอย่างน้อยหนึ่งประเภทการนำเข้า']);
        }

        $orcidId = OrcidPublicRecord::normalizeId($orcidRaw);

        $fetched = OrcidPublicRecord::fetchRecord($orcidId);
        if (empty($fetched['success']) || empty($fetched['data']) || !is_array($fetched['data'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $fetched['message'] ?? 'ดึงข้อมูล ORCID ไม่สำเร็จ',
            ]);
        }

        $record = $fetched['data'];

        $aff = ['education' => [], 'employment' => []];
        if (in_array('education', $scopes, true) || in_array('employment', $scopes, true)) {
            $aff = OrcidPublicRecord::extractEducationAndEmployment($record);
        }
        $education  = in_array('education', $scopes, true) ? ($aff['education'] ?? []) : [];
        $employment = in_array('employment', $scopes, true) ? ($aff['employment'] ?? []) : [];
        $works      = in_array('works', $scopes, true) ? OrcidPublicRecord::extractWorks($record) : [];

        $educationSection   = $this->ensureCvSectionForOrcid($cvSectionModel, $personnelId, 'education', 'ประวัติการศึกษา', $education !== []);
        $employmentSection  = $this->ensureCvSectionForOrcid($cvSectionModel, $personnelId, 'work', 'ประสบการณ์การทำงาน', $employment !== []);
        $worksSection       = $this->ensureCvSectionForOrcidWorks($cvSectionModel, $personnelId, $works !== []);

        $cvEntryModel = new CvEntryModel();
        $eduCount     = $this->upsertOrcidEntries($cvEntryModel, $educationSection, $education);
        $empCount     = $this->upsertOrcidEntries($cvEntryModel, $employmentSection, $employment);
        $worksCount   = $this->upsertOrcidEntries($cvEntryModel, $worksSection, $works);

        if ($cvSectionModel->db->fieldExists('orcid_id', 'personnel')) {
            (new PersonnelModel())->update($personnelId, ['orcid_id' => $orcidId]);
        }

        $hasAnyData = $education !== [] || $employment !== [] || $works !== [];
        $msg        = $hasAnyData
            ? 'นำเข้าจาก ORCID เรียบร้อยแล้ว'
            : 'ไม่พบรายการที่เปิดเผยใน ORCID สำหรับประเภทที่เลือก';

        return $this->response->setJSON([
            'success'            => true,
            'message'            => $msg,
            'education_count'    => $eduCount,
            'employment_count'   => $empCount,
            'works_count'        => $worksCount,
            'orcid_id'           => $orcidId,
            'scopes'             => $scopes,
        ]);
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

    /**
     * @param list<array<string,mixed>> $items
     */
    private function upsertOrcidEntries(CvEntryModel $cvEntryModel, ?array $section, array $items): int
    {
        if ($section === null || $items === []) {
            return 0;
        }

        $sectionId = (int) ($section['id'] ?? 0);
        if ($sectionId <= 0) {
            return 0;
        }

        $count = 0;
        foreach ($items as $item) {
            $putCode = $item['put_code'] ?? null;
            $existing = null;
            if ($putCode !== null && $putCode !== '') {
                $existing = $this->findCvEntryByOrcidPutCode($cvEntryModel, $sectionId, (string) $putCode);
            }
            if ($existing === null) {
                $extraMeta = isset($item['orcid_meta']) && is_array($item['orcid_meta']) ? $item['orcid_meta'] : [];
                $dedupe    = isset($extraMeta['orcid_dedupe_key']) ? (string) $extraMeta['orcid_dedupe_key'] : '';
                if ($dedupe !== '') {
                    $existing = $this->findCvEntryByOrcidDedupeKey($cvEntryModel, $sectionId, $dedupe);
                }
            }

            $start = $item['start_date'] ?? null;
            $end   = $item['end_date'] ?? null;
            $isCurrent = array_key_exists('is_current', $item)
                ? ((int) (bool) $item['is_current'])
                : (($end === null || $end === '') ? 1 : 0);

            $prev = $existing !== null ? CvEntryModel::decodeMetadata($existing['metadata'] ?? null) : [];
            $base = [
                'orcid_put_code' => $putCode,
                'source'         => 'orcid',
                'synced_at'      => date('Y-m-d H:i:s'),
            ];
            $extra = isset($item['orcid_meta']) && is_array($item['orcid_meta']) ? $item['orcid_meta'] : [];
            $meta  = array_merge($prev, $base, $extra);
            $meta  = array_filter($meta, static fn ($v) => $v !== null && $v !== '');
            $metadataJson = json_encode($meta, JSON_UNESCAPED_UNICODE);

            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                $title = 'รายการจาก ORCID';
            }

            $desc = $item['description'] ?? $item['department'] ?? '';

            $entryData = [
                'section_id'        => $sectionId,
                'title'             => mb_substr($title, 0, 500),
                'organization'      => $this->nullableString((string) ($item['organization'] ?? '')),
                'location'          => $this->nullableString((string) ($item['location'] ?? '')),
                'start_date'        => $start ?: null,
                'end_date'          => $end ?: null,
                'is_current'        => $isCurrent,
                'description'       => $this->nullableString((string) $desc),
                'metadata'          => $metadataJson,
                'visible_on_public' => 1,
            ];

            if ($existing !== null) {
                $entryData['sort_order'] = (int) ($existing['sort_order'] ?? $cvEntryModel->nextSortOrder($sectionId));
                $cvEntryModel->update((int) $existing['id'], $entryData);
            } else {
                $entryData['sort_order'] = $cvEntryModel->nextSortOrder($sectionId);
                $cvEntryModel->insert($entryData);
            }
            $count++;
        }

        return $count;
    }

    private function nullableString(string $s): ?string
    {
        $s = trim($s);

        return $s === '' ? null : $s;
    }

    private function findCvEntryByOrcidPutCode(CvEntryModel $cvEntryModel, int $sectionId, string $putCode): ?array
    {
        $rows = $cvEntryModel->where('section_id', $sectionId)->findAll();
        foreach ($rows as $row) {
            $meta = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
            if (isset($meta['orcid_put_code']) && (string) $meta['orcid_put_code'] === (string) $putCode) {
                return $row;
            }
        }

        return null;
    }

    private function findCvEntryByOrcidDedupeKey(CvEntryModel $cvEntryModel, int $sectionId, string $dedupeKey): ?array
    {
        $rows = $cvEntryModel->where('section_id', $sectionId)->findAll();
        foreach ($rows as $row) {
            $meta = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
            if (isset($meta['orcid_dedupe_key']) && (string) $meta['orcid_dedupe_key'] === $dedupeKey) {
                return $row;
            }
        }

        return null;
    }

    /**
     * หัวข้อผลงานตีพิมพ์: ใช้ research หรือ articles ที่มีอยู่ก่อน ถ้าไม่มีสร้าง research
     *
     * @return array<string,mixed>|null
     */
    private function ensureCvSectionForOrcidWorks(CvSectionModel $cvSectionModel, int $personnelId, bool $needed): ?array
    {
        if (!$needed) {
            return null;
        }

        $section = $cvSectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();

        if ($section !== null) {
            return $section;
        }

        $cvSectionModel->insert([
            'personnel_id'      => $personnelId,
            'type'              => 'research',
            'title'             => 'ผลงานตีพิมพ์',
            'description'       => null,
            'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
            'is_default'        => 0,
            'visible_on_public' => 1,
        ]);

        $newId = (int) $cvSectionModel->getInsertID();

        return $cvSectionModel->find($newId);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function ensureCvSectionForOrcid(CvSectionModel $cvSectionModel, int $personnelId, string $type, string $defaultTitle, bool $needed): ?array
    {
        if (!$needed) {
            return null;
        }

        if ($type === 'work') {
            $section = $cvSectionModel->where('personnel_id', $personnelId)
                ->groupStart()
                ->where('type', 'work')
                ->orWhere('type', 'experience')
                ->groupEnd()
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->first();
        } else {
            $section = $cvSectionModel->where('personnel_id', $personnelId)
                ->where('type', $type)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->first();
        }

        if ($section !== null) {
            return $section;
        }

        $cvSectionModel->insert([
            'personnel_id'      => $personnelId,
            'type'              => $type === 'work' ? 'work' : $type,
            'title'             => $defaultTitle,
            'description'       => null,
            'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
            'is_default'        => 0,
            'visible_on_public' => 1,
        ]);

        $newId = (int) $cvSectionModel->getInsertID();

        return $cvSectionModel->find($newId);
    }
}
