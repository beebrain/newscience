<?php

namespace App\Libraries;

/**
 * OrganizationRoles - Standardized constants for organization positions and curriculum roles
 *
 * This library provides a single source of truth for:
 * - Administrative positions (คณบดี, รองคณบดี, ผู้ช่วยคณบดี)
 * - Curriculum roles (ประธานหลักสูตร, กรรมการหลักสูตร, อาจารย์ประจำหลักสูตร)
 * - Tier classification for organization chart display
 */
class OrganizationRoles
{
    /**
     * Administrative positions in the organization
     * Tier 1-3: Dean, Vice Dean, Assistant Dean
     */
    public const POSITIONS = [
        'dean' => [
            'th' => 'คณบดี',
            'en' => 'Dean',
            'tier' => 1,
            'sort_order' => 1,
        ],
        'vice_dean' => [
            'th' => 'รองคณบดี',
            'en' => 'Vice Dean',
            'tier' => 2,
            'sort_order' => 2,
        ],
        'assistant_dean' => [
            'th' => 'ผู้ช่วยคณบดี',
            'en' => 'Assistant Dean',
            'tier' => 3,
            'sort_order' => 3,
        ],
    ];

    /**
     * Roles within a curriculum/program
     * Tier 4: Program Chair
     * Tier 5: Committee, Faculty
     */
    public const CURRICULUM_ROLES = [
        'chair' => [
            'th' => 'ประธานหลักสูตร',
            'en' => 'Program Chair',
            'tier' => 4,
            'sort_order' => 1,
        ],
        'committee' => [
            'th' => 'กรรมการหลักสูตร',
            'en' => 'Committee Member',
            'tier' => 5,
            'sort_order' => 2,
        ],
        'faculty' => [
            'th' => 'อาจารย์ประจำหลักสูตร',
            'en' => 'Faculty Member',
            'tier' => 5,
            'sort_order' => 3,
        ],
    ];

    /**
     * Tier names for display
     */
    public const TIER_NAMES = [
        1 => ['th' => 'คณบดี', 'en' => 'Dean'],
        2 => ['th' => 'รองคณบดี', 'en' => 'Vice Deans'],
        3 => ['th' => 'ผู้ช่วยคณบดี', 'en' => 'Assistant Deans'],
        4 => ['th' => 'ประธานหลักสูตร', 'en' => 'Program Chairs'],
        5 => ['th' => 'อาจารย์และบุคลากร', 'en' => 'Faculty & Staff'],
    ];

    /**
     * Determine the tier level of a personnel based on their position
     *
     * @param array $personnel Personnel data array with 'position' and optionally 'position_en'
     * @return int Tier level (1=คณบดี, 2=รอง, 3=ผู้ช่วย, 4=หัวหน้าสำนักงาน/หัวหน้าหน่วยวิจัย, 5=ประธานหลักสูตร, 6=อื่นๆ)
     */
    public static function getTier(array $personnel): int
    {
        $position = mb_strtolower(trim($personnel['position'] ?? ''));
        $positionEn = trim($personnel['position_en'] ?? '');

        if (empty($position) && empty($positionEn)) {
            return 6;
        }
        $posCheck = $position !== '' ? $position : mb_strtolower($positionEn);
        if ($posCheck === '') {
            return 6;
        }

        // Tier 4: หัวหน้าสำนักงาน / หัวหน้าหน่วยการจัดการงานวิจัย (ไม่ถือเป็นคณบดี)
        if (mb_strpos($posCheck, 'หัวหน้าสำนักงาน') !== false
            || mb_strpos($posCheck, 'หัวหน้าหน่วยจัดการ') !== false
            || mb_strpos($posCheck, 'หัวหน้าหน่วยการจัดการงานวิจัย') !== false
            || mb_strpos($posCheck, 'หัวหน้าหน่วยวิจัย') !== false
            || strpos($posCheck, 'head of office') !== false
            || strpos($posCheck, 'head of research') !== false
            || strpos($posCheck, 'head of dean') !== false) {
            return 4;
        }

        $hasDean = mb_strpos($posCheck, 'คณบดี') !== false || strpos($posCheck, 'dean') !== false;
        $hasVice = mb_strpos($posCheck, 'รอง') !== false || strpos($posCheck, 'vice') !== false || strpos($posCheck, 'associate dean') !== false;
        $hasAssistant = mb_strpos($posCheck, 'ผู้ช่วย') !== false || strpos($posCheck, 'assistant dean') !== false;
        $hasChair = mb_strpos($posCheck, 'ประธาน') !== false;

        // Tier 1: Dean (but not Vice Dean or Assistant Dean)
        if ($hasDean && !$hasVice && !$hasAssistant) {
            return 1;
        }

        // Tier 2: Vice Dean
        if ($hasDean && $hasVice) {
            return 2;
        }

        // Tier 3: Assistant Dean
        if ($hasDean && $hasAssistant) {
            return 3;
        }

        // Tier 5: Program Chair (ประธานหลักสูตร)
        if ($hasChair && (mb_strpos($posCheck, 'หลักสูตร') !== false || strpos($posCheck, 'program chair') !== false || strpos($posCheck, 'curriculum') !== false)) {
            return 5;
        }

        // Default: Faculty & Staff (tier 6)
        return 6;
    }

    /**
     * Get tier from curriculum role
     *
     * @param string|null $role Role in curriculum
     * @return int Tier level (4 or 5)
     */
    public static function getTierFromRole(?string $role): int
    {
        if (empty($role)) {
            return 5;
        }

        $role = mb_strtolower(trim($role));

        if (mb_strpos($role, 'ประธาน') !== false) {
            return 4;
        }

        return 5;
    }

    /**
     * Get position key from Thai text
     *
     * @param string $positionTh Thai position text
     * @return string|null Position key or null if not found
     */
    public static function getPositionKey(string $positionTh): ?string
    {
        $positionTh = mb_strtolower(trim($positionTh));

        foreach (self::POSITIONS as $key => $data) {
            if (mb_strpos($positionTh, mb_strtolower($data['th'])) !== false) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get curriculum role key from Thai text
     *
     * @param string $roleTh Thai role text
     * @return string|null Role key or null if not found
     */
    public static function getRoleKey(string $roleTh): ?string
    {
        $roleTh = mb_strtolower(trim($roleTh));

        foreach (self::CURRICULUM_ROLES as $key => $data) {
            if (mb_strpos($roleTh, mb_strtolower($data['th'])) !== false) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get position label in specified language
     *
     * @param string $key Position key
     * @param string $lang Language code ('th' or 'en')
     * @return string Position label
     */
    public static function getPositionLabel(string $key, string $lang = 'th'): string
    {
        return self::POSITIONS[$key][$lang] ?? $key;
    }

    /**
     * Get curriculum role label in specified language
     *
     * @param string $key Role key
     * @param string $lang Language code ('th' or 'en')
     * @return string Role label
     */
    public static function getRoleLabel(string $key, string $lang = 'th'): string
    {
        return self::CURRICULUM_ROLES[$key][$lang] ?? $key;
    }

    /**
     * Get tier name in specified language
     *
     * @param int $tier Tier level (1-5)
     * @param string $lang Language code ('th' or 'en')
     * @return string Tier name
     */
    public static function getTierName(int $tier, string $lang = 'th'): string
    {
        return self::TIER_NAMES[$tier][$lang] ?? 'อื่นๆ';
    }

    /**
     * Get all positions as options for select dropdown
     *
     * @param string $lang Language code ('th' or 'en')
     * @return array Array of [key => label]
     */
    public static function getPositionOptions(string $lang = 'th'): array
    {
        $options = [];
        foreach (self::POSITIONS as $key => $data) {
            $options[$data[$lang]] = $data[$lang];
        }
        return $options;
    }

    /**
     * Get all curriculum roles as options for select dropdown
     *
     * @param string $lang Language code ('th' or 'en')
     * @return array Array of [key => label]
     */
    public static function getRoleOptions(string $lang = 'th'): array
    {
        $options = [];
        foreach (self::CURRICULUM_ROLES as $key => $data) {
            $options[$data[$lang]] = $data[$lang];
        }
        return $options;
    }

    /**
     * Check if a position string indicates Program Chair
     *
     * @param string|null $position Position string
     * @return bool
     */
    public static function isChair(?string $position): bool
    {
        if (empty($position)) {
            return false;
        }
        return mb_strpos(mb_strtolower($position), 'ประธาน') !== false;
    }

    /**
     * Check if a position string indicates Dean (any level)
     *
     * @param string|null $position Position string
     * @return bool
     */
    public static function isDeanLevel(?string $position): bool
    {
        if (empty($position)) {
            return false;
        }
        return mb_strpos(mb_strtolower($position), 'คณบดี') !== false;
    }
}
