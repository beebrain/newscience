<?php

/**
 * รายการค่าเล่าเรียน/ค่าธรรมเนียม (tuition_fees_json) สำหรับ API / SPA
 */

if (! function_exists('tuition_fee_items_from_json_string')) {
    /**
     * @return list<array{label: string, amount: string, note: string}>
     */
    function tuition_fee_items_from_json_string(?string $json): array
    {
        $out = [];
        if ($json === null || $json === '') {
            return $out;
        }
        $dec = json_decode($json, true);
        if (! is_array($dec)) {
            return $out;
        }
        foreach ($dec as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label  = (string) ($row['label'] ?? $row['title'] ?? '');
            $amount = (string) ($row['amount'] ?? $row['value'] ?? '');
            $note   = (string) ($row['note'] ?? $row['remark'] ?? '');
            if ($label === '' && $amount === '' && $note === '') {
                continue;
            }
            $out[] = [
                'label'  => mb_substr($label, 0, 200),
                'amount' => mb_substr($amount, 0, 500),
                'note'   => mb_substr($note, 0, 500),
            ];
        }

        return array_slice($out, 0, 40);
    }
}

if (! function_exists('tuition_fees_json_normalize')) {
    function tuition_fees_json_normalize($raw): string
    {
        if ($raw === null) {
            return '[]';
        }
        if (! is_string($raw)) {
            $raw = (string) $raw;
        }
        if (trim($raw) === '') {
            return '[]';
        }
        $items = tuition_fee_items_from_json_string($raw);

        return json_encode(array_values($items), JSON_UNESCAPED_UNICODE);
    }
}
