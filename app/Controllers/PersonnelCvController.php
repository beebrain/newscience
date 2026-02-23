<?php

namespace App\Controllers;

use App\Models\PersonnelModel;
use App\Models\SiteSettingModel;

/**
 * Controller สำหรับหน้า CV อาจารย์ (แยกต่างหากจาก controller อื่น)
 * เข้าถึงจากหน้าบุคลากร: GET /personnel-cv/{email}
 */
class PersonnelCvController extends BaseController
{
    protected PersonnelModel $personnelModel;
    protected SiteSettingModel $siteSettingModel;

    public function __construct()
    {
        $this->personnelModel = new PersonnelModel();
        $this->siteSettingModel = new SiteSettingModel();
    }

    /**
     * GET /personnel-cv/{email}
     * แสดงหน้า CV ของอาจารย์
     */
    public function show(string $email): string
    {
        $email = urldecode($email);
        $person = $this->personnelModel->findByEmail($email);

        if ($person === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ไม่พบข้อมูลบุคลากร: ' . $email);
        }

        $settings = $this->siteSettingModel->getAll();
        $layout = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        // ชื่อแสดงผล
        $displayName = trim($person['academic_title'] ?? '') !== ''
            ? trim($person['academic_title']) . ' ' . trim($person['name'] ?? '')
            : trim($person['name'] ?? '');

        $displayNameEn = trim($person['academic_title_en'] ?? '') !== ''
            ? trim($person['academic_title_en']) . ' ' . trim($person['name_en'] ?? '')
            : trim($person['name_en'] ?? '');

        // รูปโปรไฟล์
        $image = trim($person['image'] ?? '');
        if ($image !== '' && strpos($image, 'http') !== 0) {
            $image = base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $image)));
        }

        $data = [
            'settings'     => $settings,
            'site_info'    => $settings,
            'layout'       => $layout,
            'page_title'   => $displayName . ' | CV',
            'meta_description' => 'ประวัติและผลงาน ' . $displayName,
            'active_page'  => 'personnel',
            'person'       => $person,
            'display_name' => $displayName,
            'display_name_en' => $displayNameEn,
            'profile_image' => $image,
        ];

        return view('pages/personnel_cv', $data);
    }
}
