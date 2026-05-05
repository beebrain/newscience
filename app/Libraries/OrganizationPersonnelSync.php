<?php

namespace App\Libraries;

use App\Models\PersonnelModel;
use App\Models\PersonnelOrgRoleModel;
use App\Models\UserModel;
use Config\Certificate as CertificateConfig;

/**
 * ซิงก์แถว personnel กับ user.faculty — คณะวิทยาศาสตร์และเทคโนโลยี =
 * เกณฑ์เดียวกับ CertOrganizerAccess (คีย์เวิร์ดใน Config\Certificate)
 *
 * ไม่ได้สร้างผ่าน organization/create แล้ว — ผู้ใช้ต้องถูกเพิ่มในหน้า admin/user-faculty
 */
class OrganizationPersonnelSync
{
    /**
     * เรียกหลังเปลี่ยน user.faculty — สร้าง/เปิดใช้ personnel ถ้าอยู่คณะฯ และปิด (inactive) ถ้าอยู่คณะอื่น/ว่าง
     */
    public static function syncAfterUserFacultyChange(int $userUid): void
    {
        if ($userUid <= 0) {
            return;
        }
        $userModel = new UserModel();
        $user      = $userModel->find($userUid);
        if ($user === null) {
            return;
        }

        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '') {
            return;
        }

        $cfg       = config(CertificateConfig::class);
        $inFaculty = CertOrganizerAccess::userFacultyMatchesOrganizerFaculty($user, $cfg);

        $pm  = new PersonnelModel();
        $row = self::findPersonnelRowForUser($pm, $user);

        if (! $inFaculty) {
            if ($row !== null && (int) ($row['id'] ?? 0) > 0) {
                $pm->skipValidation(true)->update((int) $row['id'], ['status' => 'inactive']);
            }

            return;
        }

        $payload = [
            'status'     => 'active',
            'user_email' => $email,
            'email'      => $email,
        ];
        if ($pm->db->fieldExists('user_uid', 'personnel')) {
            $payload['user_uid'] = $userUid;
        }

        if ($row !== null && (int) ($row['id'] ?? 0) > 0) {
            $pm->skipValidation(true)->update((int) $row['id'], $payload);

            return;
        }

        $nameTh = trim($userModel->getFullName($user));
        $nameEn = trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? ''));
        $title  = trim((string) ($user['title'] ?? ''));

        $insert = $payload + [
            'name'       => $nameTh !== '' ? $nameTh : $email,
            'name_en'    => $nameEn !== '' ? $nameEn : null,
            'position'   => 'อาจารย์',
            'sort_order' => self::nextSortOrder($pm),
        ];
        if ($pm->db->fieldExists('academic_title', 'personnel')) {
            $insert['academic_title'] = $title !== '' ? $title : null;
        }

        $pm->skipValidation(true)->insert($insert);
        $newId = (int) $pm->getInsertID();
        if ($newId > 0 && $pm->db->tableExists('personnel_org_roles')) {
            (new PersonnelOrgRoleModel())->replaceForPersonnel($newId, [[
                'role_kind'            => PersonnelOrgRoleRules::KIND_CURRICULUM,
                'position_title'       => 'อาจารย์',
                'program_id'           => null,
                'organization_unit_id' => null,
                'position_detail'      => null,
                'sort_order'             => 0,
            ]]);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findPersonnelRowForUser(PersonnelModel $pm, array $user): ?array
    {
        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '') {
            return null;
        }
        $row = $pm->findByEmail($email);
        if ($row !== null) {
            return $row;
        }
        $uid = (int) ($user['uid'] ?? 0);
        if ($uid > 0 && $pm->db->fieldExists('user_uid', 'personnel')) {
            return $pm->where('user_uid', $uid)->first();
        }

        return null;
    }

    private static function nextSortOrder(PersonnelModel $pm): int
    {
        $r = $pm->db->table('personnel')->selectMax('sort_order')->get()->getRowArray();

        return (int) ($r['sort_order'] ?? 0) + 1;
    }
}
