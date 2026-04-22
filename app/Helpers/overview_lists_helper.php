<?php

/**
 * วัตถุประสงค์ / คุณลักษณะบัณฑิต เก็บใน objectives / graduate_profile เป็น JSON array ของบรรทัด
 * (ฝั่งแอดมิน: รายการกรอก ไม่ต้องแก้ JSON มือ)
 */

if (! function_exists('overview_text_lines_from_db')) {
    /**
     * อ่านค่าในคอลัมน์: ถ้าเป็น JSON array ใช้ตามนั้น; ถ้าเป็นข้อความหลายบรรทัดแยกบรรทัด; ไม่ก็ [ข้อความทั้งก้อน]
     *
     * @return list<string>
     */
    function overview_text_lines_from_db(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $t = trim($value);
        if ($t === '') {
            return [];
        }
        if ($t[0] === '[') {
            $d = json_decode($t, true);
            if (is_array($d)) {
                $out = [];
                foreach ($d as $line) {
                    if (is_string($line) && trim($line) !== '') {
                        $out[] = mb_substr(trim($line), 0, 2000);
                    } elseif (is_scalar($line) && (string) $line !== '') {
                        $out[] = mb_substr(trim((string) $line), 0, 2000);
                    }
                }

                return array_slice($out, 0, 40);
            }
        }
        $parts = preg_split('/\r\n|\r|\n/', $value);
        $parts = array_values(array_filter(array_map('trim', $parts), static function ($l) { return $l !== ''; }));
        if (count($parts) > 1) {
            return array_slice(array_map(static function ($l) { return mb_substr($l, 0, 2000); }, $parts), 0, 40);
        }

        return $t !== '' ? [mb_substr($t, 0, 2000)] : [];
    }
}

if (! function_exists('overview_lines_to_json')) {
    function overview_lines_to_json(array $lines): string
    {
        $out = [];
        foreach ($lines as $line) {
            $s = is_string($line) ? trim($line) : trim((string) $line);
            if ($s === '') {
                continue;
            }
            $out[] = mb_substr($s, 0, 2000);
        }
        $out = array_slice($out, 0, 40);

        return json_encode(array_values($out), JSON_UNESCAPED_UNICODE);
    }
}

if (! function_exists('overview_lines_normalize')) {
    /**
     * รับ string จาก POST (มักเป็น JSON array ของรายการจากฟอร์ม) หรือ normalize จากข้อความ
     */
    function overview_lines_normalize($raw): string
    {
        if ($raw === null) {
            return '[]';
        }
        if (is_string($raw)) {
            $t = trim($raw);
            if ($t === '') {
                return '[]';
            }
            if ($t[0] === '[') {
                $d = json_decode($t, true);
                if (is_array($d)) {
                    return overview_lines_to_json($d);
                }
            }

            return overview_lines_to_json(overview_text_lines_from_db($raw));
        }
        if (is_array($raw)) {
            return overview_lines_to_json($raw);
        }

        return '[]';
    }
}
