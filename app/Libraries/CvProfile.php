<?php

namespace App\Libraries;

/**
 * CV section keys and Thai labels for user-editable public CV blocks.
 */
class CvProfile
{
    public const SECTION_COURSES = 'courses';
    public const SECTION_RESEARCH = 'research';
    public const SECTION_ARTICLES = 'articles';
    public const SECTION_EDUCATION = 'education_structured';
    public const SECTION_SERVICE = 'service';
    public const SECTION_OTHER = 'other';

    /** @return list<string> */
    public static function sectionKeys(): array
    {
        return [
            self::SECTION_COURSES,
            self::SECTION_RESEARCH,
            self::SECTION_ARTICLES,
            self::SECTION_EDUCATION,
            self::SECTION_SERVICE,
            self::SECTION_OTHER,
        ];
    }

    /** @return array<string, string> section_key => Thai label */
    public static function sectionLabelsTh(): array
    {
        return [
            self::SECTION_COURSES      => 'รายวิชาที่สอน',
            self::SECTION_RESEARCH     => 'งานวิจัยที่ตีพิมพ์',
            self::SECTION_ARTICLES     => 'บทความวิชาการ',
            self::SECTION_EDUCATION    => 'ประวัติการศึกษา (รายการ)',
            self::SECTION_SERVICE      => 'การบริการวิชาการ / วิทยากร',
            self::SECTION_OTHER        => 'รายละเอียดอื่นๆ',
        ];
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Default sort order for new section rows (block order on public CV).
     *
     * @return array<string, int>
     */
    public static function defaultSortOrderByKey(): array
    {
        $i = 0;

        return [
            self::SECTION_COURSES      => $i++,
            self::SECTION_EDUCATION    => $i++,
            self::SECTION_RESEARCH     => $i++,
            self::SECTION_ARTICLES     => $i++,
            self::SECTION_SERVICE      => $i++,
            self::SECTION_OTHER        => $i++,
        ];
    }
}
