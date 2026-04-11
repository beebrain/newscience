<?php

declare(strict_types=1);

if (! function_exists('cv_format_iso_date_th_be')) {
    /**
     * แปลงวันที่รูปแบบ YYYY-MM-DD (ค.ศ. ใน DB) เป็น d/m/พ.ศ. สำหรับแสดง
     */
    function cv_format_iso_date_th_be(?string $iso): string
    {
        if ($iso === null || $iso === '') {
            return '';
        }

        $d = strlen($iso) >= 10 ? substr($iso, 0, 10) : $iso;

        if ($d === '0000-00-00' || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m)) {
            return '';
        }

        $ts = strtotime($d . ' 12:00:00');
        if ($ts === false) {
            return '';
        }

        $beYear = (int) date('Y', $ts) + 543;

        return (string) ((int) date('j', $ts)) . '/' . (string) ((int) date('n', $ts)) . '/' . (string) $beYear;
    }
}

if (! function_exists('cv_format_entry_date_span_be')) {
    /**
     * ช่วงวันที่รายการ CV สำหรับแสดง (พ.ศ.) — ตรรกะเดิมกับหน้าแก้ไข: มี start ถึงแสดง, ต่อ end หรือ "ปัจจุบัน"
     *
     * @param int|string $isCurrent จากคอลัมน์ is_current
     */
    function cv_format_entry_date_span_be(?string $start, ?string $end, $isCurrent = 0): string
    {
        $start = $start !== null ? trim((string) $start) : '';
        if ($start === '' || substr($start, 0, 10) === '0000-00-00') {
            return '';
        }

        $span = cv_format_iso_date_th_be(substr($start, 0, 10));
        if ($span === '') {
            return '';
        }

        $end = $end !== null ? trim((string) $end) : '';
        if ($end !== '' && substr($end, 0, 10) !== '0000-00-00') {
            $endFmt = cv_format_iso_date_th_be(substr($end, 0, 10));
            if ($endFmt !== '') {
                $span .= '–' . $endFmt;
            }
        } elseif (! empty($isCurrent) && (int) $isCurrent === 1) {
            $span .= '–ปัจจุบัน';
        }

        return $span;
    }
}
