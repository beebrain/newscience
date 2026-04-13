<?php

namespace App\Libraries;

/**
 * สิทธิ์ผู้จัดกิจกรรม E-Certificate
 * — ผู้ใช้ Dashboard (Portal) ที่ล็อกอินแล้ว (role user / admin / …) สร้างกิจกรรมและออกใบได้
 * — ผู้ดูแลระดับคณะ (super_admin, faculty_admin) เห็นและกรองรายการทั้งคณะได้
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
     * (ทุกคนที่เข้า Portal แล้วได้ session admin_* จากระบบเว็บ — ไม่บังคับ personnel)
     */
    public static function currentMayOrganize(): bool
    {
        if (! session()->get('admin_logged_in')) {
            return false;
        }
        if ((int) (session()->get('admin_id') ?? 0) <= 0) {
            return false;
        }

        if (self::isFacultyWideViewer()) {
            return true;
        }

        $role = (string) (session()->get('admin_role') ?? '');
        if (in_array($role, ['admin', 'editor'], true)) {
            return true;
        }

        // ผู้ใช้ทั่วไป (role user) หลัง OAuth Portal — ใช้งาน E-Certificate ได้ทันที
        if ($role === 'user') {
            return true;
        }

        // บทบาทอื่นที่ยังใช้ Dashboard เดียวกัน (ถ้ามี)
        if ($role !== '') {
            return true;
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
