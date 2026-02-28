<?php

/**
 * Site Helper — คืนค่าโลโก้/ favicon จากการตั้งค่าเว็บ (Logo คณะ)
 * Load with: helper('site');
 */

if (!function_exists('favicon_url')) {
    /**
     * คืน URL ของ favicon / โลโก้คณะ (จาก site_settings.logo หรือค่าเริ่มต้น)
     *
     * @return string URL เต็มสำหรับใช้ใน <link rel="icon" href="...">
     */
    function favicon_url(): string
    {
        try {
            $model = new \App\Models\SiteSettingModel();
            $logo = $model->getValue('logo', '');
            $logo = is_string($logo) ? trim($logo) : '';
            if ($logo !== '') {
                return strpos($logo, 'http') === 0 ? $logo : base_url($logo);
            }
        } catch (\Throwable $e) {
            // fallback to default
        }
        return base_url('assets/images/logo250.png');
    }
}
