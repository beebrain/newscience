<?php

namespace App\Libraries;

/**
 * ประเภทการเผยแพร่ผลงาน — เก็บค่าเดียวกับ Research Record API ฟิลด์ publication_type
 * (มักเป็นรหัสภาษาอังกฤษ เช่น journal, conference) รองรับรหัสอื่นที่ RR ส่งมาโดยแสดงเป็นข้อความดิบ
 */
final class RrPublicationType
{
    /** @var array<string,string> รหัส normalize เป็นตัวพิมพ์เล็ก => ป้ายไทย */
    private const LABEL_TH = [
        'journal'                  => 'บทความในวารสารวิชาการ',
        'journal_article'          => 'บทความในวารสารวิชาการ',
        'article'                  => 'บทความในวารสารวิชาการ',
        'conference'               => 'บทความในการประชุมวิชาการ / proceeding',
        'conference_proceeding'    => 'บทความในการประชุมวิชาการ / proceeding',
        'conference_paper'         => 'บทความในการประชุมวิชาการ / proceeding',
        'proceeding'               => 'บทความในการประชุมวิชาการ / proceeding',
        'proceedings'              => 'บทความในการประชุมวิชาการ / proceeding',
        'international_conference' => 'บทความในการประชุมวิชาการนานาชาติ',
        'national_conference'      => 'บทความในการประชุมวิชาการในประเทศ',
        'book'                     => 'หนังสือ / ตำรา',
        'book_chapter'             => 'ตอนในหนังสือ',
        'chapter'                  => 'ตอนในหนังสือ',
        'patent'                   => 'สิทธิบัตร / อนุสิทธิบัตร',
        'utility_model'            => 'อนุสิทธิบัตรการประดิษฐ์',
        'technical_report'         => 'รายงานวิชาการ',
        'report'                   => 'รายงานวิชาการ',
        'thesis'                   => 'วิทยานิพนธ์',
        'dissertation'             => 'วิทยานิพนธ์',
        'magazine'                 => 'บทความในวารสารทั่วไป / นิตยสาร',
        'newspaper'                => 'บทความในหนังสือพิมพ์',
        'presentation'             => 'การนำเสนอ (Oral / Poster)',
        'poster'                   => 'โปสเตอร์วิชาการ',
        'creative_work'            => 'ผลงานสร้างสรรค์ / ศิลปะ',
        'dataset'                  => 'ชุดข้อมูลวิจัย',
        'software'                 => 'ซอฟต์แวร์ / โปรแกรม',
        'policy_brief'             => 'ข้อเสนอเชิงนโยบาย',
        'other'                    => 'อื่นๆ',
        'others'                   => 'อื่นๆ',
        'อื่นๆ'                      => 'อื่นๆ',
    ];

    public static function labelTh(?string $code): string
    {
        if ($code === null) {
            return '';
        }
        $t = trim((string) $code);
        if ($t === '') {
            return '';
        }
        $k = mb_strtolower($t, 'UTF-8');

        return self::LABEL_TH[$k] ?? $t;
    }

    /**
     * กลุ่มตัวเลือกสำหรับ optgroup — รหัสเก็บตรงกับ Research Record publication_type
     *
     * @return array<string, list<array{value:string,label:string}>>
     */
    public static function selectOptionGroups(): array
    {
        return [
            'วารสาร / บทความวิชาการ' => [
                ['value' => 'journal', 'label' => 'บทความในวารสารวิชาการ (journal)'],
                ['value' => 'journal_article', 'label' => 'บทความในวารสาร — รูปแบบอื่น (journal_article)'],
                ['value' => 'article', 'label' => 'บทความในวารสาร (article)'],
                ['value' => 'magazine', 'label' => 'วารสารทั่วไป / นิตยสาร (magazine)'],
                ['value' => 'newspaper', 'label' => 'บทความในหนังสือพิมพ์ (newspaper)'],
            ],
            'การประชุมวิชาการ' => [
                ['value' => 'conference', 'label' => 'บทความในการประชุม / proceeding (conference)'],
                ['value' => 'conference_paper', 'label' => 'Conference paper (conference_paper)'],
                ['value' => 'conference_proceeding', 'label' => 'Proceeding (conference_proceeding)'],
                ['value' => 'international_conference', 'label' => 'การประชุมวิชาการนานาชาติ (international_conference)'],
                ['value' => 'national_conference', 'label' => 'การประชุมวิชาการในประเทศ (national_conference)'],
                ['value' => 'presentation', 'label' => 'การนำเสนอ Oral / บรรยาย (presentation)'],
                ['value' => 'poster', 'label' => 'โปสเตอร์วิชาการ (poster)'],
            ],
            'หนังสือ / ตำรา' => [
                ['value' => 'book', 'label' => 'หนังสือ / ตำรา (book)'],
                ['value' => 'book_chapter', 'label' => 'ตอนในหนังสือ / ตำรา (book_chapter)'],
                ['value' => 'chapter', 'label' => 'ตอนในหนังสือ (chapter)'],
            ],
            'สิทธิบัตร รายงาน วิทยานิพนธ์' => [
                ['value' => 'patent', 'label' => 'สิทธิบัตร / อนุสิทธิบัตร (patent)'],
                ['value' => 'utility_model', 'label' => 'อนุสิทธิบัตรการประดิษฐ์ (utility_model)'],
                ['value' => 'technical_report', 'label' => 'รายงานวิชาการ (technical_report)'],
                ['value' => 'report', 'label' => 'รายงาน (report)'],
                ['value' => 'thesis', 'label' => 'วิทยานิพนธ์ (thesis)'],
                ['value' => 'dissertation', 'label' => 'วิทยานิพนธ์ระดับสูง (dissertation)'],
            ],
            'ผลงานรูปแบบอื่น' => [
                ['value' => 'creative_work', 'label' => 'ผลงานสร้างสรรค์ / ศิลปะ (creative_work)'],
                ['value' => 'dataset', 'label' => 'ชุดข้อมูลวิจัย (dataset)'],
                ['value' => 'software', 'label' => 'ซอฟต์แวร์ / โปรแกรม (software)'],
                ['value' => 'policy_brief', 'label' => 'ข้อเสนอเชิงนโยบาย (policy_brief)'],
                ['value' => 'other', 'label' => 'อื่นๆ (other)'],
            ],
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public static function selectOptions(): array
    {
        $flat = [];
        foreach (self::selectOptionGroups() as $opts) {
            foreach ($opts as $o) {
                $flat[] = $o;
            }
        }

        return $flat;
    }

    /**
     * ค่าที่อนุญาตจากฟอร์ม: รายการหลัก หรือรหัสสไตล์ RR (a-z 0-9 _ - .) หรือ "อื่นๆ"
     */
    public static function isValidPublicationTypeCode(string $v): bool
    {
        $v = trim($v);
        if ($v === '') {
            return false;
        }
        if ($v === 'อื่นๆ') {
            return true;
        }
        foreach (self::selectOptions() as $o) {
            if ($o['value'] === $v) {
                return true;
            }
        }

        return (bool) preg_match('/^[a-z][a-z0-9_\-.]{0,79}$/i', $v);
    }
}
