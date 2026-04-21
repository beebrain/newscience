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
     * - Object: { "intro": "...", "standards": [...], "mapping": [...] }
     * - Array เดี่ยว: [ {...}, ... ] → ถือเป็น standards
     *
     * @return array{intro: string, standards: list<array>, mapping: list<array>}
     */
    function parse_learning_standards_json(?string $raw): array
    {
        $out = [
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
