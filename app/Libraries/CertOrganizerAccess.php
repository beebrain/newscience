<?php

namespace App\Libraries;

use App\Models\UserModel;
use Config\Certificate as CertificateConfig;

/**
 * สิทธิ์ผู้จัดกิจกรรม E-Certificate
 * — ต้องเป็นผู้ใช้ใน table user ที่คอลัมน์ faculty ระบุสังกัดคณะวิทยาศาสตร์และเทคโนโลยี (ยกเว้นโรลใน certOrganizerFacultyBypassRoles)
 * — ผู้ดูแลระดับคณะ (super_admin, faculty_admin) เห็นและกรองรายการทั้งคณะได้เมื่อผ่าน currentMayOrganize()
 */
class CertOrganizerAccess
{
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * ผู้ใช้ปัจจุบัน (session admin_*) ดูรายการกิจกรรมทั้งคณะได้หรือไม่
     */
    public static function isFacultyWideViewer(): bool
    {
        $role = (string) (session()->get('admin_role') ?? '');

        return in_array($role, ['super_admin', 'faculty_admin'], true);
    }

    /**
     * ผู้ใช้ปัจจุบันสร้าง/จัดการกิจกรรมใบรับรองจาก Dashboard ได้หรือไม่
     * อ่านแถว user จาก session admin_id — ตรวจคอลัมน์ faculty ให้ตรงกับคณะวิทยาศาสตร์และเทคโนโลยี (ดู Config\Certificate)
     */
    public static function currentMayOrganize(): bool
    {
        if (! session()->get('admin_logged_in')) {
            return false;
        }
        $uid = (int) (session()->get('admin_id') ?? 0);
        if ($uid <= 0) {
            return false;
        }

        $role = (string) (session()->get('admin_role') ?? '');
        $cfg  = config(CertificateConfig::class);

        foreach ($cfg->certOrganizerFacultyBypassRoles as $bypassRole) {
            if ($bypassRole !== '' && $role === $bypassRole) {
                return true;
            }
        }

        $user = (new UserModel())->find($uid);
        if (! $user) {
            return false;
        }

        return self::userFacultyMatchesOrganizerFaculty($user, $cfg);
    }

    /**
     * user.faculty มีคีย์เวิร์ดคณะวิทยาศาสตร์และเทคโนโลยีหรือไม่
     *
     * @param array<string, mixed> $userRow แถวจากตาราง user
     */
    public static function userFacultyMatchesOrganizerFaculty(array $userRow, ?CertificateConfig $cfg = null): bool
    {
        $cfg ??= config(CertificateConfig::class);
        $faculty = trim((string) ($userRow['faculty'] ?? ''));
        if ($faculty === '') {
            return false;
        }
        $haystack = mb_strtolower($faculty, 'UTF-8');
        foreach ($cfg->certOrganizerFacultyKeywords as $keyword) {
            $keyword = trim((string) $keyword);
            if ($keyword === '') {
                continue;
            }
            if (mb_stripos($haystack, mb_strtolower($keyword, 'UTF-8'), 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * ผู้ใช้ปัจจุบันแก้ไข event นี้ได้หรือไม่ (ตาม created_by หรือสิทธิ์กว้าง)
     */
    public static function mayAccessEvent(array $event): bool
    {
        if (self::isFacultyWideViewer()) {
            return true;
        }

        $role = (string) (session()->get('admin_role') ?? '');
        if (in_array($role, ['admin', 'editor'], true)) {
            return true;
        }

        $uid = (int) (session()->get('admin_id') ?? 0);
        if ($uid <= 0) {
            return false;
        }

        $createdBy = isset($event['created_by']) ? (int) $event['created_by'] : 0;

        return $createdBy === $uid && self::currentMayOrganize();
    }

    /**
     * Dashboard: ผู้จัดทั่วไปเห็นเฉพาะกิจกรรมของตน — ผู้ดูแลคณะเห็นทั้งคณะ
     */
    public static function isDashboardOrganizerOnly(): bool
    {
        if (self::isFacultyWideViewer()) {
            return false;
        }

        return self::currentMayOrganize();
    }
}
