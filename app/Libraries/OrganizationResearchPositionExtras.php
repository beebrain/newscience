<?php

namespace App\Libraries;

/**
 * ตำแหน่งเพิ่มเติมในหมวด «หน่วยจัดการงานวิจัย» เท่านั้น — เก็บที่ writable/config
 */
class OrganizationResearchPositionExtras
{
    private static function filePath(): string
    {
        return WRITEPATH . 'config/organization_research_positions.json';
    }

    /**
     * @return list<string>
     */
    public static function getAll(): array
    {
        $path = self::filePath();
        if (! is_file($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $item) {
            $s = is_string($item) ? trim($item) : '';
            if ($s !== '') {
                $out[] = $s;
            }
        }
        $seen  = [];
        $uniq  = [];
        foreach ($out as $s) {
            $k = mb_strtolower($s, 'UTF-8');
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $uniq[]   = $s;
        }

        return $uniq;
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public static function add(string $label): array
    {
        $label = trim($label);
        if ($label === '') {
            return ['ok' => false, 'message' => 'กรุณากรอกชื่อตำแหน่ง'];
        }
        if (mb_strlen($label) > 200) {
            return ['ok' => false, 'message' => 'ชื่อตำแหน่งยาวเกิน 200 ตัวอักษร'];
        }
        foreach (self::getAll() as $ex) {
            if (mb_strtolower($ex, 'UTF-8') === mb_strtolower($label, 'UTF-8')) {
                return ['ok' => false, 'message' => 'มีตำแหน่งนี้ในรายการแล้ว'];
            }
        }
        $merged   = array_merge(self::getAll(), [$label]);
        $dir      = dirname(self::filePath());
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $written = @file_put_contents(
            self::filePath(),
            json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        if ($written === false) {
            return ['ok' => false, 'message' => 'บันทึกไฟล์ตำแหน่งเพิ่มเติมไม่สำเร็จ'];
        }

        return ['ok' => true, 'message' => 'เพิ่มตำแหน่งในรายการตัวเลือกแล้ว'];
    }
}
