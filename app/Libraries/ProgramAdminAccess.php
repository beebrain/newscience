<?php

namespace App\Libraries;

use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\ProgramModel;
use App\Models\UserModel;
use App\Models\UserSystemAccessModel;

/**
 * สิทธิ์เข้าโซน program-admin (แก้ไขเว็บหลักสูตร / Content Builder)
 *
 * - super_admin: ทุกหลักสูตร (ตาม Dashboard)
 * - สิทธิ์ระบบ program_admin ใน user_system_access
 * - role admin: เข้าได้ (สอดคล้องเดิม)
 * - อาจารย์/บุคลากรที่ user.email ผูกกับ personnel และมีแถวใน personnel_programs
 *   หรือเป็น chair_personnel_id ของหลักสูตรใดหลักสูตรหนึ่ง
 */
class ProgramAdminAccess
{
    public static function canAccessProgramAdminArea(int $uid, ?array $user = null): bool
    {
        if ($user === null) {
            $user = (new UserModel())->find($uid);
        }
        if (!$user) {
            return false;
        }

        $role = (string) ($user['role'] ?? '');

        if ($role === 'super_admin') {
            return true;
        }

        $accessModel = new UserSystemAccessModel();
        if ($accessModel->hasAccess($uid, 'program_admin', 'view')) {
            return true;
        }

        if ($role === 'admin') {
            return true;
        }

        $email = trim(strtolower((string) ($user['email'] ?? '')));
        if ($email === '') {
            return false;
        }

        $personnelModel = new PersonnelModel();
        $personnel = $personnelModel->findByEmail($email);
        if (!$personnel || empty($personnel['id'])) {
            return false;
        }
        $personnelId = (int) $personnel['id'];

        $ppModel = new PersonnelProgramModel();
        if ($ppModel->getByPersonnelId($personnelId) !== []) {
            return true;
        }

        $programModel = new ProgramModel();
        return $programModel->where('chair_personnel_id', $personnelId)->first() !== null;
    }
}
