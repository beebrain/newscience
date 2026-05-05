<?php

/**
 * Program page JSON helpers (PLO/ELO, มาตรฐานการเรียนรู้)
 * Load with: helper('program_page');
 */

if (! function_exists('parse_learning_standards_json')) {
    /**
     * Normalize learning_standards_json จาก DB เป็นโครงสร้างคงที่สำหรับ API/View
     *
     * รูปแบบที่รองรับ:
     * - New: { "mode": "this_year"|"next_year", "domains": [{domain,name,items},...] }
     * - Old: { "intro": "...", "standards": [...], "mapping": [...] }
     * - Array เดี่ยว: [ {...}, ... ] → ถือเป็น standards (legacy)
     *
     * @return array{mode: string, domains: list<array>, intro: string, standards: list<array>, mapping: list<array>}
     */
    function parse_learning_standards_json(?string $raw): array
    {
        $out = [
            'mode'      => 'this_year',
            'domains'   => [],
            'intro'     => '',
            'standards' => [],
            'mapping'   => [],
        ];

        if ($raw === null || trim($raw) === '') {
            return $out;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $out;
        }

        // New domain-based format
        if (isset($decoded['domains']) && is_array($decoded['domains'])) {
            $out['mode']    = isset($decoded['mode']) ? (string) $decoded['mode'] : 'this_year';
            $out['domains'] = $decoded['domains'];
            return $out;
        }

        // Old object format
        if (isset($decoded['standards']) || isset($decoded['intro']) || isset($decoded['mapping'])) {
            $out['intro'] = isset($decoded['intro']) ? (string) $decoded['intro'] : '';
            $out['standards'] = isset($decoded['standards']) && is_array($decoded['standards'])
                ? $decoded['standards'] : [];
            $out['mapping'] = isset($decoded['mapping']) && is_array($decoded['mapping'])
                ? $decoded['mapping'] : [];
        } else {
            $isList = array_keys($decoded) === range(0, count($decoded) - 1);
            if ($isList) {
                $out['standards'] = $decoded;
            }
        }

        foreach ($out['standards'] as $i => $row) {
            if (! is_array($row)) {
                unset($out['standards'][$i]);

                continue;
            }
            $n                           = $i + 1;
            $code                        = trim((string) ($row['code'] ?? ''));
            $row['code']                 = $code !== '' ? $code : ('LS' . $n);
            $row['category']             = (string) ($row['category'] ?? '');
            $row['title']                = (string) ($row['title'] ?? $row['category'] ?? ('มาตรฐานการเรียนรู้ ' . $n));
            $detail                      = (string) ($row['detail'] ?? $row['description'] ?? '');
            $row['detail']               = $detail;
            $row['summary']              = (string) ($row['summary'] ?? (mb_strlen($detail) > 160 ? mb_substr($detail, 0, 160) . '…' : $detail));
            $out['standards'][$i]        = $row;
        }
        $out['standards'] = array_values($out['standards']);

        foreach ($out['mapping'] as $j => $m) {
            if (! is_array($m)) {
                unset($out['mapping'][$j]);

                continue;
            }
            $sc = trim((string) ($m['standard_code'] ?? $m['standard'] ?? ''));
            $pr = trim((string) ($m['plo_refs'] ?? $m['plo'] ?? $m['plo_labels'] ?? ''));
            $out['mapping'][$j] = [
                'standard_code' => $sc,
                'plo_refs'      => $pr,
            ];
        }
        $out['mapping'] = array_values($out['mapping']);

        return $out;
    }
}

if (! function_exists('program_hero_public_url')) {
    /**
     * URL สาธารณะสำหรับรูปหน้าปก/Hero บนเว็บหลักสูตร
     * ลำดับ: program_pages.hero_image → fallback programs.image
     * รองรับ path แบบ programs/{id}/hero/…, ค่าที่มี segment serve/uploads/ แทรก, และลิงก์ http(s)
     */
    function program_hero_public_url(?string $pageHeroImage, ?string $programTableImage): string
    {
        $raw = trim((string) $pageHeroImage);
        if ($raw === '') {
            $raw = trim((string) $programTableImage);
        }
        if ($raw === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        $path = str_replace('\\', '/', $raw);
        $path = ltrim($path, '/');

        if (preg_match('#(^|/)serve/uploads/(.+)$#i', $path, $m)) {
            return base_url('serve/uploads/' . ltrim($m[2], '/'));
        }

        while (stripos($path, 'serve/uploads/') === 0) {
            $path = substr($path, strlen('serve/uploads/'));
            $path = ltrim($path, '/');
        }
        if (stripos($path, 'writable/uploads/') === 0) {
            $path = substr($path, strlen('writable/uploads/'));
            $path = ltrim($path, '/');
        }

        return base_url('serve/uploads/' . $path);
    }
}
