<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiteSettingModel;

class Settings extends BaseController
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SiteSettingModel();
    }

    /**
     * แสดงหน้า Settings ทั้งหมดแยกตามหมวดหมู่
     */
    public function index()
    {
        $settings = $this->settingModel->findAll();
        
        // จัดกลุ่ม settings ตาม category
        $groupedSettings = [];
        foreach ($settings as $setting) {
            $category = $setting['category'] ?? 'general';
            if (!isset($groupedSettings[$category])) {
                $groupedSettings[$category] = [];
            }
            $groupedSettings[$category][] = $setting;
        }

        // กำหนดชื่อหมวดหมู่แบบอ่านง่าย
        $categoryLabels = [
            'general' => 'ทั่วไป',
            'site' => 'เว็บไซต์',
            'contact' => 'ติดต่อ',
            'social' => 'โซเชียลมีเดีย',
            'seo' => 'SEO',
            'appearance' => 'การแสดงผล'
        ];

        $data = [
            'page_title' => 'ตั้งค่าเว็บไซต์',
            'groupedSettings' => $groupedSettings,
            'categoryLabels' => $categoryLabels
        ];

        return view('admin/settings/index', $data);
    }

    /**
     * บันทึกการตั้งค่าทั้งหมด
     */
    public function store()
    {
        $settings = $this->request->getPost('settings');
        
        if (empty($settings) || !is_array($settings)) {
            return redirect()->back()
                ->with('error', 'ไม่พบข้อมูลการตั้งค่า');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($settings as $id => $value) {
            // ตรวจสอบ setting มีอยู่จริง
            $existing = $this->settingModel->find($id);
            if (!$existing) {
                $errorCount++;
                continue;
            }

            // จัดการค่าตาม type
            $settingValue = $this->processValueByType($value, $existing['setting_type']);

            $updated = $this->settingModel->update($id, [
                'setting_value' => $settingValue
            ]);

            if ($updated) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        // Clear cache เพื่อให้ค่าใหม่มีผลทันที
        \Config\Services::cache()->delete('site_settings_all');

        if ($errorCount === 0) {
            return redirect()->to(base_url('admin/settings'))
                ->with('success', "บันทึกการตั้งค่าสำเร็จ ({$successCount} รายการ)");
        } else {
            return redirect()->to(base_url('admin/settings'))
                ->with('warning', "บันทึกสำเร็จ {$successCount} รายการ, ไม่สำเร็จ {$errorCount} รายการ");
        }
    }

    /**
     * สร้าง setting ใหม่
     */
    public function create()
    {
        $data = [
            'page_title' => 'เพิ่มการตั้งค่าใหม่'
        ];

        return view('admin/settings/create', $data);
    }

    /**
     * บันทึก setting ใหม่
     */
    public function storeNew()
    {
        $rules = [
            'setting_key' => 'required|max_length[100]|is_unique[site_settings.setting_key]',
            'setting_value' => 'permit_empty',
            'setting_type' => 'required|in_list[text,textarea,image,json,boolean]',
            'category' => 'required|max_length[50]',
            'description' => 'permit_empty|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'setting_key' => $this->request->getPost('setting_key'),
            'setting_value' => $this->request->getPost('setting_value'),
            'setting_type' => $this->request->getPost('setting_type'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description')
        ];

        // จัดการค่าตาม type
        $data['setting_value'] = $this->processValueByType($data['setting_value'], $data['setting_type']);

        if ($this->settingModel->insert($data)) {
            \Config\Services::cache()->delete('site_settings_all');
            return redirect()->to(base_url('admin/settings'))
                ->with('success', 'เพิ่มการตั้งค่าสำเร็จ');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'ไม่สามารถเพิ่มการตั้งค่าได้');
    }

    /**
     * ลบ setting
     */
    public function delete($id)
    {
        $setting = $this->settingModel->find($id);
        
        if (!$setting) {
            return redirect()->to(base_url('admin/settings'))
                ->with('error', 'ไม่พบการตั้งค่า');
        }

        // ไม่ให้ลบถ้าเป็น system critical settings
        $protectedKeys = ['site_name', 'site_url'];
        if (in_array($setting['setting_key'], $protectedKeys)) {
            return redirect()->to(base_url('admin/settings'))
                ->with('error', 'ไม่สามารถลบการตั้งค่าสำคัญของระบบได้');
        }

        if ($this->settingModel->delete($id)) {
            \Config\Services::cache()->delete('site_settings_all');
            return redirect()->to(base_url('admin/settings'))
                ->with('success', 'ลบการตั้งค่าสำเร็จ');
        }

        return redirect()->to(base_url('admin/settings'))
            ->with('error', 'ไม่สามารถลบการตั้งค่าได้');
    }

    /**
     * จัดการค่าตาม type
     */
    private function processValueByType($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                // ตรวจสอบว่าเป็น valid JSON
                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $value;
                    }
                }
                return json_encode($value);
            case 'image':
                // จัดการ upload รูปภาพ
                $file = $this->request->getFile("settings_files[{$value}]");
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    $file->move($uploadPath, $newName);
                    return 'uploads/settings/' . $newName;
                }
                return $value;
            default:
                return $value;
        }
    }

    /**
     * Initialize default settings
     * สร้างค่าเริ่มต้นสำหรับ settings ที่จำเป็น
     */
    public function initDefaults()
    {
        $defaults = [
            ['site_name', 'New Science Website', 'text', 'general', 'ชื่อเว็บไซต์'],
            ['site_description', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'textarea', 'general', 'คำอธิบายเว็บไซต์'],
            ['contact_email', 'admin@example.com', 'text', 'contact', 'อีเมลติดต่อ'],
            ['contact_phone', '', 'text', 'contact', 'เบอร์โทรศัพท์'],
            ['facebook_url', '', 'text', 'social', 'Facebook URL'],
            ['youtube_url', '', 'text', 'social', 'YouTube URL'],
            ['meta_keywords', '', 'textarea', 'seo', 'คำค้นหา SEO'],
            ['footer_text', '© ' . date('Y') . ' All rights reserved.', 'textarea', 'appearance', 'ข้อความท้ายเว็บ']
        ];

        $created = 0;
        foreach ($defaults as $default) {
            $existing = $this->settingModel->where('setting_key', $default[0])->first();
            if (!$existing) {
                $this->settingModel->insert([
                    'setting_key' => $default[0],
                    'setting_value' => $default[1],
                    'setting_type' => $default[2],
                    'category' => $default[3],
                    'description' => $default[4]
                ]);
                $created++;
            }
        }

        if ($created > 0) {
            \Config\Services::cache()->delete('site_settings_all');
            return redirect()->to(base_url('admin/settings'))
                ->with('success', "สร้างค่าเริ่มต้น {$created} รายการ");
        }

        return redirect()->to(base_url('admin/settings'))
            ->with('info', 'มีค่าเริ่มต้นครบถ้วนแล้ว');
    }
}
