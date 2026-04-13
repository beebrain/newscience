<?php

namespace App\Controllers;

use App\Libraries\CvProfile;
use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;
use App\Models\SiteSettingModel;

/**
 * Controller สำหรับหน้า CV อาจารย์ (แยกต่างหากจาก controller อื่น)
 * เข้าถึงจากหน้าบุคลากร: GET /personnel-cv/{email}
 */
class PersonnelCvController extends BaseController
{
    /**
     * GET /personnel-cv/{email}
     * แสดงหน้า CV ของอาจารย์ (cv_sections + cv_entries แบบ researchRecord)
     */
    public function show(string $email): string
    {
        $personnelModel = new PersonnelModel();
        $siteSettingModel = new SiteSettingModel();

        $email  = CvProfile::normalizeEmail(urldecode($email));
        $person = $email !== '' ? $personnelModel->findByEmail($email) : null;

        if ($person === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ไม่พบข้อมูลบุคลากร: ' . $email);
        }

        $settings = $siteSettingModel->getAll();
        $layout = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        // ชื่อแสดงผล
        $displayName = trim($person['academic_title'] ?? '') !== ''
            ? trim($person['academic_title']) . ' ' . trim($person['name'] ?? '')
            : trim($person['name'] ?? '');

        $displayNameEn = trim($person['academic_title_en'] ?? '') !== ''
            ? trim($person['academic_title_en']) . ' ' . trim($person['name_en'] ?? '')
            : trim($person['name_en'] ?? '');

        // รูปโปรไฟล์เฉพาะ CV (ไม่ใช้รูปบัญชี user)
        $image = trim((string) ($person['cv_profile_image'] ?? ''));
        if ($image !== '' && strpos($image, 'http') !== 0) {
            $image = base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $image)));
        }

        $cvSections = [];
        $personnelId = (int) ($person['id'] ?? 0);
        $cvSectionModel = new CvSectionModel();
        $cvEntryModel = new CvEntryModel();

        if ($personnelId > 0 && $cvSectionModel->db->tableExists('cv_sections') && CvEntryModel::isTablePresent($cvSectionModel->db)) {
            $sections = $cvSectionModel->where('personnel_id', $personnelId)
                ->where('visible_on_public', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            foreach ($sections as $section) {
                $entries = $cvEntryModel->where('section_id', (int) $section['id'])
                    ->where('visible_on_public', 1)
                    ->orderedForCvDisplay()
                    ->findAll();

                if ($entries === []) {
                    continue;
                }

                foreach ($entries as &$en) {
                    $en['metadata_array'] = CvEntryModel::decodeMetadata($en['metadata'] ?? null);
                }
                unset($en);

                $cvSections[] = [
                    'title'   => $section['title'] ?? '',
                    'type'    => $section['type'] ?? '',
                    'entries' => $entries,
                ];
            }
        }

        helper('cv');

        $data = [
            'settings'         => $settings,
            'site_info'        => $settings,
            'layout'           => $layout,
            'page_title'       => $displayName . ' | CV',
            'meta_description' => 'ประวัติและผลงาน ' . $displayName,
            'active_page'      => 'personnel',
            'person'           => $person,
            'display_name'     => $displayName,
            'display_name_en'  => $displayNameEn,
            'profile_image'    => $image,
            'cv_sections'      => $cvSections,
        ];

        return view('pages/personnel_cv', $data);
    }
}
