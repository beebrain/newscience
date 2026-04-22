<?php

/**
 * อาชีพแบบการ์ด (careers_json) สำหรับ API / SPA
 */

if (! function_exists('career_icon_whitelist')) {
    /**
     * @return list<string>
     */
    function career_icon_whitelist(): array
    {
        return ['cpu', 'chart', 'search', 'code', 'users', 'rocket', 'mortar', 'target', 'briefcase', 'book'];
    }
}

if (! function_exists('career_items_from_json_string')) {
    /**
     * @return list<array{title: string, desc: string, icon: string}>
     */
    function career_items_from_json_string(?string $json): array
    {
        $out = [];
        if ($json === null || $json === '') {
            return $out;
        }
        $dec = json_decode($json, true);
        if (! is_array($dec)) {
            return $out; // ว่างหรือ JSON ไม่ถูกต้อง
        }
        $allowed = array_fill_keys(career_icon_whitelist(), true);
        foreach ($dec as $row) {
            if (! is_array($row)) {
                continue;
            }
            $icon = strtolower(preg_replace('/[^a-z_]/', '', (string) ($row['icon'] ?? 'rocket')));
            if (! isset($allowed[$icon])) {
                $icon = 'rocket';
            }
            $title = (string) ($row['title'] ?? '');
            $desc  = (string) ($row['description'] ?? $row['desc'] ?? '');
            if ($title === '' && $desc === '') {
                continue;
            }
            $out[] = [
                'title' => mb_substr($title, 0, 200),
                'desc'  => mb_substr($desc, 0, 2000),
                'icon'  => $icon,
            ];
        }

        return array_slice($out, 0, 24);
    }
}

if (! function_exists('career_json_normalize')) {
    function career_json_normalize($raw): string
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
        $items = career_items_from_json_string($raw);

        return json_encode(array_values($items), JSON_UNESCAPED_UNICODE);
    }
}
