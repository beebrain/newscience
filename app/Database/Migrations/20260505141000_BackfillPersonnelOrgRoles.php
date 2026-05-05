<?php

namespace App\Database\Migrations;

use App\Libraries\PersonnelOrgRoleRules;
use CodeIgniter\Database\Migration;

class BackfillPersonnelOrgRoles extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('personnel_org_roles') || ! $this->db->tableExists('personnel')) {
            return;
        }

        $personnel = $this->db->table('personnel')->select('id, position, position_detail, program_id, organization_unit_id')->get()->getResultArray();
        $ppRows    = $this->db->tableExists('personnel_programs')
            ? $this->db->table('personnel_programs')->orderBy('personnel_id', 'ASC')->orderBy('sort_order', 'ASC')->get()->getResultArray()
            : [];
        $ppByPid = [];
        foreach ($ppRows as $row) {
            $pid = (int) ($row['personnel_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $ppByPid[$pid][] = $row;
        }

        foreach ($personnel as $p) {
            $pid = (int) ($p['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $existing = $this->db->table('personnel_org_roles')->where('personnel_id', $pid)->countAllResults();
            if ($existing > 0) {
                continue;
            }

            $sort = 0;
            $pos  = trim((string) ($p['position'] ?? ''));
            if ($pos !== '') {
                $kind = PersonnelOrgRoleRules::inferRoleKind($pos);
                $this->db->table('personnel_org_roles')->insert([
                    'personnel_id'           => $pid,
                    'role_kind'              => $kind,
                    'position_title'         => $pos,
                    'program_id'             => $this->programIdForRole($kind, (int) ($p['program_id'] ?? 0)),
                    'organization_unit_id'   => $this->nullableInt($p['organization_unit_id'] ?? null),
                    'position_detail'        => $this->emptyToNull($p['position_detail'] ?? null),
                    'sort_order'             => $sort++,
                    'created_at'             => date('Y-m-d H:i:s'),
                    'updated_at'             => date('Y-m-d H:i:s'),
                ]);
            }

            $seenPrograms = [];
            if ($pos !== '' && (int) ($p['program_id'] ?? 0) > 0 && $this->isCurriculumPosition($pos)) {
                $seenPrograms[(int) $p['program_id']] = true;
            }

            foreach ($ppByPid[$pid] ?? [] as $pp) {
                $progId = (int) ($pp['program_id'] ?? 0);
                if ($progId <= 0) {
                    continue;
                }
                $roleIn = trim((string) ($pp['role_in_curriculum'] ?? ''));
                $title  = (mb_strpos($roleIn, 'ประธาน') !== false) ? 'ประธานหลักสูตร' : 'อาจารย์ประจำหลักสูตร';
                if (isset($seenPrograms[$progId])) {
                    $dup = $this->db->table('personnel_org_roles')
                        ->where('personnel_id', $pid)
                        ->where('program_id', $progId)
                        ->where('role_kind', PersonnelOrgRoleRules::KIND_CURRICULUM)
                        ->countAllResults();
                    if ($dup > 0) {
                        continue;
                    }
                }
                $seenPrograms[$progId] = true;
                $this->db->table('personnel_org_roles')->insert([
                    'personnel_id'           => $pid,
                    'role_kind'              => PersonnelOrgRoleRules::KIND_CURRICULUM,
                    'position_title'         => $title,
                    'program_id'             => $progId,
                    'organization_unit_id'   => null,
                    'position_detail'        => null,
                    'sort_order'             => $sort++,
                    'created_at'             => date('Y-m-d H:i:s'),
                    'updated_at'             => date('Y-m-d H:i:s'),
                ]);
            }

            if ($sort === 0) {
                $this->db->table('personnel_org_roles')->insert([
                    'personnel_id'           => $pid,
                    'role_kind'              => PersonnelOrgRoleRules::KIND_CURRICULUM,
                    'position_title'         => 'อาจารย์',
                    'program_id'             => null,
                    'organization_unit_id'   => $this->nullableInt($p['organization_unit_id'] ?? null),
                    'position_detail'        => null,
                    'sort_order'             => 0,
                    'created_at'             => date('Y-m-d H:i:s'),
                    'updated_at'             => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function programIdForRole(string $kind, int $programId): ?int
    {
        if ($kind !== PersonnelOrgRoleRules::KIND_CURRICULUM) {
            return null;
        }

        return $programId > 0 ? $programId : null;
    }

    private function isCurriculumPosition(string $pos): bool
    {
        return $pos === 'อาจารย์' || $pos === 'ประธานหลักสูตร' || $pos === 'อาจารย์ประจำหลักสูตร';
    }

    private function nullableInt($v): ?int
    {
        $n = (int) $v;

        return $n > 0 ? $n : null;
    }

    private function emptyToNull($v): ?string
    {
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    public function down(): void
    {
        if ($this->db->tableExists('personnel_org_roles')) {
            $this->db->table('personnel_org_roles')->truncate();
        }
    }
}
