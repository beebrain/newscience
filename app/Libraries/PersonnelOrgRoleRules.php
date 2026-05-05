<?php

namespace App\Libraries;

use App\Models\PersonnelProgramModel;

/**
 * กฎ validate และซิงก์ personnel_programs จาก personnel_org_roles
 */
class PersonnelOrgRoleRules
{
    public const KIND_EXECUTIVE   = 'executive';
    public const KIND_RESEARCH    = 'research';
    public const KIND_OFFICE      = 'office';
    public const KIND_CURRICULUM  = 'curriculum';

    /**
     * @return list<string>
     */
    public static function roleKinds(): array
    {
        return [self::KIND_EXECUTIVE, self::KIND_RESEARCH, self::KIND_OFFICE, self::KIND_CURRICULUM];
    }

    /**
     * อนุมาน role_kind จากชื่อตำแหน่ง (เมื่อฟอร์มไม่ส่ง kind)
     */
    public static function inferRoleKind(string $positionTitle): string
    {
        $t = trim($positionTitle);
        if ($t === '') {
            return self::KIND_CURRICULUM;
        }
        if (mb_strpos($t, 'คณบดี') !== false || mb_strpos($t, 'รองคณบดี') !== false || mb_strpos($t, 'ผู้ช่วยคณบดี') !== false) {
            return self::KIND_EXECUTIVE;
        }
        if (mb_strpos($t, 'เจ้าหน้าที่') !== false || mb_strpos($t, 'หัวหน้าสำนักงาน') !== false) {
            return self::KIND_OFFICE;
        }
        if (mb_strpos($t, 'หัวหน้าหน่วย') !== false || mb_strpos($t, 'กรรมการหน่วย') !== false) {
            return self::KIND_RESEARCH;
        }
        if ($t === 'อาจารย์' || $t === 'ประธานหลักสูตร' || $t === 'อาจารย์ประจำหลักสูตร') {
            return self::KIND_CURRICULUM;
        }
        foreach (OrganizationResearchPositionExtras::getAll() as $extra) {
            if ($t === $extra) {
                return self::KIND_RESEARCH;
            }
        }

        return self::KIND_RESEARCH;
    }

    /**
     * @param list<array{role_kind?: string, position_title?: string, program_id?: int|null, organization_unit_id?: int|null, position_detail?: string|null, sort_order?: int}> $rows
     * @return string|null error message
     */
    public static function validateRows(array $rows): ?string
    {
        $allowed = OrganizationPositionCatalog::getAllowedTitles();
        foreach ($rows as $i => $row) {
            $title = trim((string) ($row['position_title'] ?? ''));
            if ($title === '') {
                return 'กรุณาระบุตำแหน่งในทุกแถวบทบาท';
            }
            if (! in_array($title, $allowed, true)) {
                return 'ตำแหน่ง "' . $title . '" ไม่อยู่ในรายการที่อนุญาต';
            }
            $kind = trim((string) ($row['role_kind'] ?? ''));
            if ($kind === '' || ! in_array($kind, self::roleKinds(), true)) {
                $kind = self::inferRoleKind($title);
            }
            $programId = isset($row['program_id']) ? (int) $row['program_id'] : 0;
            $detail    = trim((string) ($row['position_detail'] ?? ''));

            if ($kind === self::KIND_CURRICULUM) {
                if ($programId <= 0) {
                    return 'บทบาทหลักสูตร (' . $title . ') ต้องเลือกหลักสูตร (สาขา)';
                }
            }
            if ($kind === self::KIND_EXECUTIVE) {
                if ($detail === '') {
                    return 'ตำแหน่งผู้บริหาร (' . $title . ') ต้องระบุรายละเอียดตำแหน่ง (เช่น ฝ่าย)';
                }
            }
            if ($kind === self::KIND_OFFICE && mb_strpos($title, 'เจ้าหน้าที่') !== false) {
                if ($detail === '') {
                    return 'ตำแหน่งเจ้าหน้าที่ ต้องระบุรายละเอียดตำแหน่ง';
                }
            }
        }

        return null;
    }

    /**
     * ตำแหน่งสำหรับคำนวณ tier (OrganizationRoles::getTier) — เลือกแถวที่ tier เลขน้อยที่สุด
     *
     * @param list<array<string, mixed>> $roles
     */
    public static function effectivePositionForTier(array $roles, ?string $fallbackPosition): string
    {
        if ($roles === []) {
            return trim((string) $fallbackPosition);
        }
        $bestTier = 99;
        $bestTitle = '';
        foreach ($roles as $r) {
            $title = trim((string) ($r['position_title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $tier = OrganizationRoles::getTier(['position' => $title, 'position_en' => '']);
            if ($tier < $bestTier) {
                $bestTier = $tier;
                $bestTitle = $title;
            }
        }

        return $bestTitle !== '' ? $bestTitle : trim((string) $fallbackPosition);
    }

    /**
     * สรุปคอลัมน์ legacy บน personnel หลังบันทึก roles
     *
     * @param list<array<string, mixed>> $roles
     * @return array{position: string|null, program_id: int|null, organization_unit_id: int|null, position_detail: string|null}
     */
    public static function summarizeLegacyPersonnelColumns(array $roles): array
    {
        if ($roles === []) {
            return [
                'position'               => null,
                'program_id'             => null,
                'organization_unit_id'   => null,
                'position_detail'        => null,
            ];
        }
        usort($roles, static function ($a, $b) {
            return ((int) ($a['sort_order'] ?? 0)) - ((int) ($b['sort_order'] ?? 0));
        });
        $position = trim((string) ($roles[0]['position_title'] ?? ''));
        $position = $position !== '' ? $position : null;

        $programId           = null;
        $organizationUnitId = null;
        $positionDetail      = null;

        $primaryPid = self::resolvePrimaryProgramIdFromOrgRoles($roles);
        if ($primaryPid > 0) {
            $programId = $primaryPid;
        }

        foreach ($roles as $r) {
            $kind = trim((string) ($r['role_kind'] ?? ''));
            if ($kind === '' && isset($r['position_title'])) {
                $kind = self::inferRoleKind((string) $r['position_title']);
            }
            $pid = (int) ($r['program_id'] ?? 0);
            if ($programId === null && $kind === self::KIND_CURRICULUM && $pid > 0) {
                $programId = $pid;
            }
            $ou = (int) ($r['organization_unit_id'] ?? 0);
            if ($ou > 0 && $organizationUnitId === null) {
                $organizationUnitId = $ou;
            }
            $d = trim((string) ($r['position_detail'] ?? ''));
            if ($d !== '' && $positionDetail === null) {
                $positionDetail = $d;
            }
        }

        return [
            'position'               => $position,
            'program_id'             => $programId,
            'organization_unit_id'   => $organizationUnitId > 0 ? $organizationUnitId : null,
            'position_detail'        => $positionDetail,
        ];
    }

    /**
     * เลือกสาขาหลักสำหรับ personnel_programs: เช็คบ็อกซ์ is_primary_program ก่อน แล้วค่อยแถว curriculum ที่ sort_order น้อยสุด
     *
     * @param list<array<string, mixed>> $roles
     */
    public static function resolvePrimaryProgramIdFromOrgRoles(array $roles): int
    {
        $candidates = [];
        foreach ($roles as $r) {
            $kind = trim((string) ($r['role_kind'] ?? ''));
            if ($kind === '' && isset($r['position_title'])) {
                $kind = self::inferRoleKind((string) $r['position_title']);
            }
            if ($kind !== self::KIND_CURRICULUM) {
                continue;
            }
            $pid = (int) ($r['program_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $candidates[] = [
                'program_id'  => $pid,
                'sort_order'  => (int) ($r['sort_order'] ?? 0),
                'is_primary'  => ! empty($r['is_primary_program']),
            ];
        }
        $marked = array_values(array_filter($candidates, static fn ($c) => $c['is_primary']));
        if ($marked !== []) {
            usort($marked, static fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);

            return (int) $marked[0]['program_id'];
        }
        if ($candidates === []) {
            return 0;
        }
        usort($candidates, static fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        return (int) $candidates[0]['program_id'];
    }

    /**
     * สร้างรายการสำหรับ PersonnelProgramModel::setProgramsForPersonnelWithPrimary จากแถว curriculum
     *
     * @param list<array<string, mixed>> $roles
     * @param int $primaryProgramId ถ้า 0 จะ resolve จากแถว (เช็คบ็อกซ์สาขาหลัก / sort_order)
     * @return list<array{program_id: int, role_in_curriculum: string|null, is_primary: bool}>
     */
    public static function buildProgramRolesFromOrgRoles(array $roles, int $primaryProgramId = 0): array
    {
        if ($primaryProgramId <= 0) {
            $primaryProgramId = self::resolvePrimaryProgramIdFromOrgRoles($roles);
        }
        $byProgram = [];
        foreach ($roles as $r) {
            $kind = trim((string) ($r['role_kind'] ?? ''));
            if ($kind === '' && isset($r['position_title'])) {
                $kind = self::inferRoleKind((string) $r['position_title']);
            }
            if ($kind !== self::KIND_CURRICULUM) {
                continue;
            }
            $programId = (int) ($r['program_id'] ?? 0);
            if ($programId <= 0) {
                continue;
            }
            $title = trim((string) ($r['position_title'] ?? ''));
            $roleIn = null;
            if (mb_strpos($title, 'ประธาน') !== false) {
                $roleIn = 'ประธานหลักสูตร';
            } elseif ($title === 'อาจารย์ประจำหลักสูตร') {
                $roleIn = 'อาจารย์ประจำหลักสูตร';
            } else {
                $roleIn = 'อาจารย์ประจำหลักสูตร';
            }
            $existing = $byProgram[$programId] ?? null;
            if ($existing === null) {
                $byProgram[$programId] = $roleIn;
            } else {
                if (mb_strpos($roleIn, 'ประธาน') !== false) {
                    $byProgram[$programId] = $roleIn;
                }
            }
        }
        $out = [];
        foreach ($byProgram as $pid => $roleIn) {
            $out[] = [
                'program_id'           => $pid,
                'role_in_curriculum'   => $roleIn,
                'is_primary'           => $primaryProgramId > 0 && $pid === $primaryProgramId,
            ];
        }
        if ($primaryProgramId > 0) {
            $hasPrimary = false;
            foreach ($out as $o) {
                if (! empty($o['is_primary'])) {
                    $hasPrimary = true;
                    break;
                }
            }
            if (! $hasPrimary && $out !== []) {
                $out[0]['is_primary'] = true;
            }
        }

        return $out;
    }

    public static function syncPersonnelPrograms(int $personnelId, array $roles, int $primaryProgramId = 0): void
    {
        $ppModel = new PersonnelProgramModel();
        if (! $ppModel->db->tableExists('personnel_programs')) {
            return;
        }
        $programRows = self::buildProgramRolesFromOrgRoles($roles, $primaryProgramId);
        $ppModel->setProgramsForPersonnelWithPrimary($personnelId, $programRows);
    }

    /**
     * ประธานหลักสูตรของสาขานี้จาก org_roles หรือตำแหน่ง legacy
     */
    public static function hasChairCurriculumRoleForProgram(array $roles, int $programId, ?string $legacyPosition): bool
    {
        foreach ($roles as $r) {
            if ((int) ($r['program_id'] ?? 0) !== $programId) {
                continue;
            }
            $kind  = trim((string) ($r['role_kind'] ?? ''));
            $title = (string) ($r['position_title'] ?? '');
            if ($kind === '') {
                $kind = self::inferRoleKind($title);
            }
            if ($kind !== self::KIND_CURRICULUM) {
                continue;
            }
            if (mb_strpos($title, 'ประธาน') !== false) {
                return true;
            }
        }

        return mb_strpos((string) $legacyPosition, 'ประธานหลักสูตร') !== false;
    }
}
