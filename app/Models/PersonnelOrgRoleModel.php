<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonnelOrgRoleModel extends Model
{
    protected $table            = 'personnel_org_roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $allowedFields    = [
        'personnel_id',
        'role_kind',
        'position_title',
        'program_id',
        'organization_unit_id',
        'position_detail',
        'sort_order',
    ];
    protected bool $updateOnlyChanged = false;

    public function getByPersonnelId(int $personnelId): array
    {
        if (! $this->db->tableExists($this->table)) {
            return [];
        }

        return $this->where('personnel_id', $personnelId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * @param int[] $personnelIds
     * @return array<int, list<array<string, mixed>>> keyed by personnel_id
     */
    public function getGroupedByPersonnelIds(array $personnelIds): array
    {
        if (! $this->db->tableExists($this->table)) {
            return [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $personnelIds), fn ($id) => $id > 0)));
        if ($ids === []) {
            return [];
        }
        $rows = $this->whereIn('personnel_id', $ids)
            ->orderBy('personnel_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
        $out = [];
        foreach ($rows as $row) {
            $pid = (int) ($row['personnel_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if (! isset($out[$pid])) {
                $out[$pid] = [];
            }
            $out[$pid][] = $row;
        }

        return $out;
    }

    /**
     * Replace all rows for one personnel (caller runs in transaction).
     */
    public function replaceForPersonnel(int $personnelId, array $rows): void
    {
        if (! $this->db->tableExists($this->table)) {
            return;
        }
        $this->where('personnel_id', $personnelId)->delete();
        foreach ($rows as $i => $row) {
            $this->insert([
                'personnel_id'           => $personnelId,
                'role_kind'              => $row['role_kind'],
                'position_title'         => $row['position_title'],
                'program_id'             => $row['program_id'] ?? null,
                'organization_unit_id'   => $row['organization_unit_id'] ?? null,
                'position_detail'        => $row['position_detail'] ?? null,
                'sort_order'             => (int) ($row['sort_order'] ?? $i),
            ]);
        }
    }
}
