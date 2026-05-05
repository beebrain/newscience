<?php

namespace App\Libraries;

/**
 * CV section keys and Thai labels for user-editable public CV blocks.
 * รวมชุดคำนำหน้าชื่อมาตรฐาน — ใช้กับ personnel (หลักสำหรับหน้าเว็บ/CV) และซิงก์บัญชี user
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
     * คำนำหน้าชื่อ (ไทย) value => label — ใช้กับ user.title
     *
     * @return array<string, string>
     */
    public static function academicTitleOptionsTh(): array
    {
        return [
            '' => '— ไม่ระบุ —',
            'นาย' => 'นาย',
            'นาง' => 'นาง',
            'นางสาว' => 'นางสาว',
            'ดร.' => 'ดร.',
            'อ.' => 'อ.',
            'ผศ.' => 'ผศ.',
            'รศ.' => 'รศ.',
            'ศ.' => 'ศ.',
            'ผศ.ดร.' => 'ผศ.ดร.',
            'รศ.ดร.' => 'รศ.ดร.',
            'ศ.ดร.' => 'ศ.ดร.',
            'อาจารย์' => 'อาจารย์',
            'ผู้ช่วยศาสตราจารย์' => 'ผู้ช่วยศาสตราจารย์',
            'ผู้ช่วยศาสตราจารย์ ดร.' => 'ผู้ช่วยศาสตราจารย์ ดร.',
            'รองศาสตราจารย์' => 'รองศาสตราจารย์',
            'รองศาสตราจารย์ ดร.' => 'รองศาสตราจารย์ ดร.',
            'ศาสตราจารย์' => 'ศาสตราจารย์',
            'ศาสตราจารย์ ดร.' => 'ศาสตราจารย์ ดร.',
        ];
    }

    /**
     * คำนำหน้าชื่อ (อังกฤษ) value => label — ใช้เมื่อต้องเลือกค่าอังกฤษโดยตรงในระบบ (เช่น รายงาน/ฟอร์มที่ไม่อิง user.title)
     *
     * @return array<string, string>
     */
    public static function academicTitleOptionsEn(): array
    {
        return [
            '' => '— Not specified —',
            'Mr.' => 'Mr.',
            'Mrs.' => 'Mrs.',
            'Miss' => 'Miss',
            'Dr.' => 'Dr.',
            'Lecturer' => 'Lecturer',
            'Asst. Prof.' => 'Asst. Prof.',
            'Asst. Prof. Dr.' => 'Asst. Prof. Dr.',
            'Assoc. Prof.' => 'Assoc. Prof.',
            'Assoc. Prof. Dr.' => 'Assoc. Prof. Dr.',
            'Prof.' => 'Prof.',
            'Prof. Dr.' => 'Prof. Dr.',
        ];
    }

    /**
     * แปลงคำนำหน้าไทย (ค่าเดียวกับ user.title) เป็นรูปแบบอังกฤษสำหรับแสดงสาธารณะ
     * ไม่พบในแผนที่ → คืนค่าว่าง
     */
    public static function mapAcademicTitleThToEn(string $titleTh): string
    {
        $t = trim($titleTh);
        if ($t === '') {
            return '';
        }

        /** @var array<string, string> */
        static $map = [
            'นาย' => 'Mr.',
            'นาง' => 'Mrs.',
            'นางสาว' => 'Miss',
            'ดร.' => 'Dr.',
            'อ.' => 'Lecturer',
            'ผศ.' => 'Asst. Prof.',
            'รศ.' => 'Assoc. Prof.',
            'ศ.' => 'Prof.',
            'ผศ.ดร.' => 'Asst. Prof. Dr.',
            'รศ.ดร.' => 'Assoc. Prof. Dr.',
            'ศ.ดร.' => 'Prof. Dr.',
            'อาจารย์' => 'Lecturer',
            'ผู้ช่วยศาสตราจารย์' => 'Asst. Prof.',
            'ผู้ช่วยศาสตราจารย์ ดร.' => 'Asst. Prof. Dr.',
            'รองศาสตราจารย์' => 'Assoc. Prof.',
            'รองศาสตราจารย์ ดร.' => 'Assoc. Prof. Dr.',
            'ศาสตราจารย์' => 'Prof.',
            'ศาสตราจารย์ ดร.' => 'Prof. Dr.',
        ];

        return $map[$t] ?? '';
    }

    /**
     * ตรวจค่า user.title / คำนำหน้าไทยที่บันทึกได้ (ว่างได้)
     */
    public static function isAllowedUserTitle(?string $title): bool
    {
        $t = trim((string) $title);
        if ($t === '') {
            return true;
        }

        return array_key_exists($t, self::academicTitleOptionsTh());
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
