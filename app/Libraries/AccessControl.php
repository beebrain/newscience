<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Models\SystemModel;
use App\Models\UserSystemAccessModel;

/**
 * AccessControl Library - ระบบจัดการสิทธิ์การเข้าถึงระบบย่อยแบบรวมศูนย์
 * 
 * ใช้ตรวจสอบและกำหนดสิทธิ์การเข้าถึงระบบต่างๆ แทนที่การเช็คจาก user.role/user.edoc โดยตรง
 * 
 * @package App\Libraries
 */
class AccessControl
{
    /**
     * เช็คว่า user มีสิทธิ์เข้าระบบนี้หรือไม่
     * 
     * @param int    $uid       User ID
     * @param string $systemSlug Slug ของระบบ (เช่น 'edoc', 'ecert', 'admin_core')
     * @param string $minLevel   ระดับสิทธิ์ขั้นต่ำ: 'view', 'manage', 'admin'
     * @return bool
     */
    public static function hasAccess(int $uid, string $systemSlug, string $minLevel = 'view'): bool
    {
        // ดึงข้อมูล user
        $userModel = new UserModel();
        $user = $userModel->find($uid);

        if (!$user) {
            return false;
        }

        // super_admin เข้าได้ทุกระบบโดยอัตโนมัติ
        if ($user['role'] === 'super_admin') {
            return true;
        }

        // admin_core: faculty_admin, admin, editor เข้าได้โดยอัตโนมัติ
        if ($systemSlug === 'admin_core' && in_array($user['role'], ['faculty_admin', 'admin', 'editor'], true)) {
            return true;
        }

        // เช็คจาก user_system_access
        $accessModel = new UserSystemAccessModel();
        return $accessModel->hasAccess($uid, $systemSlug, $minLevel);
    }

    /**
     * เช็คว่า user เป็น super_admin หรือไม่
     */
    public static function isSuperAdmin(int $uid): bool
    {
        $userModel = new UserModel();
        $user = $userModel->find($uid);
        return $user && $user['role'] === 'super_admin';
    }

    /**
     * เช็คว่า user เป็น faculty_admin หรือไม่
     */
    public static function isFacultyAdmin(int $uid): bool
    {
        $userModel = new UserModel();
        $user = $userModel->find($uid);
        return $user && $user['role'] === 'faculty_admin';
    }

    /**
     * ดึงรายการระบบทั้งหมดที่ user เข้าถึงได้ พร้อมระดับสิทธิ์
     * 
     * @param int $uid User ID
     * @return array รายการระบบ [slug => level, ...]
     */
    public static function getUserSystems(int $uid): array
    {
        // ดึงข้อมูล user
        $userModel = new UserModel();
        $user = $userModel->find($uid);

        if (!$user) {
            return [];
        }

        // super_admin เข้าได้ทุกระบบ
        if ($user['role'] === 'super_admin') {
            $systemModel = new SystemModel();
            $systems = $systemModel->getAllActive();
            $result = [];
            foreach ($systems as $system) {
                $result[$system['slug']] = 'admin';
            }
            return $result;
        }

        // faculty_admin, admin, editor เข้า admin_core ได้
        $baseAccess = [];
        if (in_array($user['role'], ['faculty_admin', 'admin', 'editor'], true)) {
            $baseAccess['admin_core'] = 'admin';
        }

        // รวมกับสิทธิ์จาก user_system_access
        $accessModel = new UserSystemAccessModel();
        $accessMap = $accessModel->getUserAccessMap($uid);

        return array_merge($baseAccess, $accessMap);
    }

    /**
     * กำหนดสิทธิ์การเข้าถึงระบบให้ user
     * 
     * @param int       $uid         User ID
     * @param string    $systemSlug  Slug ของระบบ
     * @param string    $level       ระดับสิทธิ์: 'view', 'manage', 'admin'
     * @param int|null  $grantedBy   UID ของผู้ที่กำหนดสิทธิ์ (null = system)
     * @return bool
     */
    public static function grantAccess(int $uid, string $systemSlug, string $level = 'view', ?int $grantedBy = null): bool
    {
        // ไม่อนุญาตให้กำหนดสิทธิ์ super_admin (super_admin เข้าได้ทุกอย่างโดยอัตโนมัติ)
        if (self::isSuperAdmin($uid)) {
            return true; // ไม่ต้องบันทึก แต่ถือว่าสำเร็จ
        }

        $accessModel = new UserSystemAccessModel();
        return $accessModel->grantAccess($uid, $systemSlug, $level, $grantedBy);
    }

    /**
     * ยกเลิกสิทธิ์การเข้าถึงระบบของ user
     * 
     * @param int    $uid        User ID
     * @param string $systemSlug Slug ของระบบ
     * @return bool
     */
    public static function revokeAccess(int $uid, string $systemSlug): bool
    {
        $accessModel = new UserSystemAccessModel();
        return $accessModel->revokeAccess($uid, $systemSlug);
    }

    /**
     * อัปเดตสิทธิ์หลายระบบพร้อมกัน
     * 
     * @param int   $uid      User ID
     * @param array $accesses รายการสิทธิ์ [system_slug => level, ...]
     * @param int|null $grantedBy UID ของผู้ที่กำหนดสิทธิ์
     * @return array ผลลัพธ์ [system_slug => success/fail]
     */
    public static function updateMultipleAccess(int $uid, array $accesses, ?int $grantedBy = null): array
    {
        $results = [];

        foreach ($accesses as $systemSlug => $level) {
            if ($level === null || $level === 'none') {
                // ยกเลิกสิทธิ์
                $results[$systemSlug] = self::revokeAccess($uid, $systemSlug);
            } else {
                // กำหนด/อัปเดตสิทธิ์
                $results[$systemSlug] = self::grantAccess($uid, $systemSlug, $level, $grantedBy);
            }
        }

        return $results;
    }

    /**
     * ดึงระดับสิทธิ์ของ user ในระบบนั้น
     * 
     * @param int    $uid        User ID
     * @param string $systemSlug Slug ของระบบ
     * @return string|null 'view', 'manage', 'admin' หรือ null (ไม่มีสิทธิ์)
     */
    public static function getAccessLevel(int $uid, string $systemSlug): ?string
    {
        // ดึงข้อมูล user
        $userModel = new UserModel();
        $user = $userModel->find($uid);

        if (!$user) {
            return null;
        }

        // super_admin มีสิทธิ์ admin ในทุกระบบ
        if ($user['role'] === 'super_admin') {
            return 'admin';
        }

        // faculty_admin, admin, editor มีสิทธิ์ admin ใน admin_core
        if ($systemSlug === 'admin_core' && in_array($user['role'], ['faculty_admin', 'admin', 'editor'], true)) {
            return 'admin';
        }

        $accessModel = new UserSystemAccessModel();
        return $accessModel->getAccessLevel($uid, $systemSlug);
    }

    /**
     * ดึงรายการ user ทั้งหมดที่มีสิทธิ์ในระบบนั้น
     * 
     * @param string $systemSlug Slug ของระบบ
     * @return array
     */
    public static function getSystemUsers(string $systemSlug): array
    {
        $accessModel = new UserSystemAccessModel();
        return $accessModel->getSystemUsers($systemSlug);
    }

    /**
     * Migrate ข้อมูลสิทธิ์เดิมจาก user.edoc และ user.admin_edoc ไปยัง user_system_access
     * ใช้ครั้งเดียวหลังสร้างตารางใหม่
     * 
     * @return array ผลลัพธ์การ migrate
     */
    public static function migrateLegacyAccess(): array
    {
        $accessModel = new UserSystemAccessModel();
        return $accessModel->migrateFromUserTable();
    }

    /**
     * เช็คว่า user สามารถจัดการสิทธิ์ของ user อื่นได้หรือไม่
     * 
     * @param int $managerUid  UID ของผู้จัดการ
     * @param int $targetUid   UID ของผู้ถูกจัดการ
     * @return bool
     */
    public static function canManageUserAccess(int $managerUid, int $targetUid): bool
    {
        $userModel = new UserModel();
        
        $manager = $userModel->find($managerUid);
        $target = $userModel->find($targetUid);

        if (!$manager || !$target) {
            return false;
        }

        // super_admin จัดการได้ทั้งหมด
        if ($manager['role'] === 'super_admin') {
            return true;
        }

        // faculty_admin จัดการได้เฉพาะ user ใน program เดียวกัน
        if ($manager['role'] === 'faculty_admin') {
            return $target['role'] !== 'super_admin' 
                && $target['program_id'] == $manager['program_id'];
        }

        // admin/editor จัดการ user ธรรมดาได้ (ไม่ใช่ super_admin, faculty_admin)
        if (in_array($manager['role'], ['admin', 'editor'], true)) {
            return !in_array($target['role'], ['super_admin', 'faculty_admin'], true);
        }

        return false;
    }
}
