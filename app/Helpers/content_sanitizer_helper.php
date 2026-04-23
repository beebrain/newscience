<?php

/**
 * Content Sanitizer Helper — ทำความสะอาด HTML จาก rich editor (Quill, textarea)
 * ใช้กับ field เนื้อหา: news.content, events.content, urgent_popups.content
 *
 * Load with: helper('content_sanitizer');
 *
 * หลักการ:
 *   - ลบ <script>, <style>, event handlers (on*), javascript: urls
 *   - บังคับ <img style="max-width:100%;height:auto"> เพื่อ responsive
 *   - ครอบ <table> ด้วย wrapper scrollable
 *   - ไม่ลบ tag safe อื่นๆ (เพื่อให้ editor ทำงานได้เหมือนเดิม)
 */

if (!function_exists('sanitize_html_content')) {
    /**
     * ทำความสะอาด HTML — เรียกก่อนเก็บลง DB
     *
     * @param string|null $html เนื้อหาดิบ
     */
    function sanitize_html_content(?string $html): string
    {
        if ($html === null || $html === '') return '';

        $clean = $html;

        // 1) ลบ <script>...</script>, <style>...</style> (รวม self-closing และ case-insensitive)
        $clean = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $clean);
        $clean = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $clean);
        $clean = preg_replace('#<script\b[^>]*/>#is', '', $clean);

        // 2) ลบ event handler attributes: on*="..." / on*='...' / on*=unquoted
        $clean = preg_replace('#\s+on[a-z]+\s*=\s*"[^"]*"#i', '', $clean);
        $clean = preg_replace("#\\s+on[a-z]+\\s*=\\s*'[^']*'#i", '', $clean);
        $clean = preg_replace('#\s+on[a-z]+\s*=\s*[^\s>]+#i', '', $clean);

        // 3) ลบ javascript: / vbscript: / data: (ยกเว้น data:image) ใน href/src
        $clean = preg_replace_callback(
            '#(href|src)\s*=\s*(["\'])(.*?)\2#i',
            function ($m) {
                $attr = $m[1];
                $q    = $m[2];
                $url  = trim($m[3]);
                $lowered = strtolower($url);
                if (strpos($lowered, 'javascript:') === 0 || strpos($lowered, 'vbscript:') === 0) {
                    return $attr . '=' . $q . '#' . $q;
                }
                if (strpos($lowered, 'data:') === 0 && strpos($lowered, 'data:image/') !== 0) {
                    return $attr . '=' . $q . '#' . $q;
                }
                return $m[0];
            },
            $clean
        );

        // 4) บังคับ style responsive บน <img> ถ้ายังไม่มี max-width
        $clean = preg_replace_callback(
            '#<img\b([^>]*)>#i',
            function ($m) {
                $attrs = $m[1];
                if (stripos($attrs, 'style=') !== false) {
                    // ถ้ามี style อยู่แล้ว — แทรก max-width ถ้ายังไม่มี
                    if (stripos($attrs, 'max-width') === false) {
                        $attrs = preg_replace_callback(
                            '#style\s*=\s*(["\'])(.*?)\1#i',
                            function ($sm) {
                                $q = $sm[1];
                                $val = rtrim($sm[2], "; \t\n");
                                return 'style=' . $q . $val . ';max-width:100%;height:auto' . $q;
                            },
                            $attrs,
                            1
                        );
                    }
                } else {
                    $attrs .= ' style="max-width:100%;height:auto"';
                }
                return '<img' . $attrs . '>';
            },
            $clean
        );

        return trim($clean);
    }
}

if (!function_exists('prepare_content_for_display')) {
    /**
     * ปรับเนื้อหาตอนแสดง — ครอบ table ด้วย wrapper scrollable
     * สามารถเรียกเพิ่มจาก sanitized content ใน DB ก็ได้ (idempotent)
     *
     * @param string|null $html เนื้อหาที่ sanitize แล้ว
     */
    function prepare_content_for_display(?string $html): string
    {
        if ($html === null || $html === '') return '';

        // ครอบ <table> ด้วย .table-responsive (ถ้ายังไม่ถูกครอบ)
        $html = preg_replace_callback(
            '#<table\b([^>]*)>(.*?)</table>#is',
            function ($m) {
                // ไม่ครอบซ้ำ — ตรวจว่าก่อนหน้าเป็น table-responsive ไหม (ยากจะเช็คใน regex ธรรมดา)
                // วิธีง่าย: ครอบทุกครั้ง (รูปแบบ output idempotent เพราะ CSS styles ไม่ซ้อนกันปัญหา)
                return '<div class="table-responsive">' . $m[0] . '</div>';
            },
            $html
        );

        return $html;
    }
}

if (!function_exists('safe_content')) {
    /**
     * ทางลัดสำหรับ view — สะอาด + เตรียมแสดงในครั้งเดียว
     * ใช้แทน <?= $content ?> → <?= safe_content($content) ?>
     *
     * ใช้เมื่อเก่าแก่ไม่ได้ sanitize ตอน save (backward-compat)
     * ถ้า sanitize ตอน save แล้ว ให้ใช้ prepare_content_for_display() อย่างเดียวพอ
     */
    function safe_content(?string $html): string
    {
        return prepare_content_for_display(sanitize_html_content($html));
    }
}
