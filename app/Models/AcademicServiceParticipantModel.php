<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicServiceParticipantModel extends Model
{
    protected $table            = 'academic_service_participants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'academic_service_id',
        'user_uid',
        'role',
        'display_name',
        'program_name',
        'sort_order',
    ];
    protected $useTimestamps = false;

    /**
     * Get all participants for a service (with user names when user_uid is set)
     */
    public function getByServiceId(int $serviceId): array
    {
        $participants = $this->where('academic_service_id', $serviceId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($participants)) {
            return [];
        }

        $uids = array_filter(array_column($participants, 'user_uid'));
        $users = [];
        if (! empty($uids)) {
            $userModel = model(\App\Models\UserModel::class);
            $userRows  = $userModel->whereIn('uid', $uids)->findAll();
            foreach ($userRows as $u) {
                $users[(int) $u['uid']] = $u;
            }
        }

        foreach ($participants as &$p) {
            $uid = isset($p['user_uid']) ? (int) $p['user_uid'] : null;
            if ($uid && isset($users[$uid])) {
                $u = $users[$uid];
                $p['display_label'] = trim(($u['tf_name'] ?? '') . ' ' . ($u['tl_name'] ?? '')) ?: trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
                $p['email'] = $u['email'] ?? null;
            } else {
                $p['display_label'] = $p['display_name'] ?? '-';
                $p['email']         = null;
            }
        }

        return $participants;
    }

    /**
     * Replace all participants for a service (delete existing, insert new)
     *
     * @param int   $serviceId
     * @param array $data Array of [ user_uid?, display_name?, program_name?, role ]
     */
    public function syncParticipants(int $serviceId, array $data): void
    {
        $this->where('academic_service_id', $serviceId)->delete();
        $sortOrder = 0;
        foreach ($data as $row) {
            if (empty($row['user_uid']) && empty(trim($row['display_name'] ?? ''))) {
                continue;
            }
            $this->insert([
                'academic_service_id' => $serviceId,
                'user_uid'           => ! empty($row['user_uid']) ? (int) $row['user_uid'] : null,
                'role'               => $row['role'] ?? 'co_participant',
                'display_name'       => $row['display_name'] ?? null,
                'program_name'       => $row['program_name'] ?? null,
                'sort_order'         => $sortOrder++,
            ]);
        }
    }

    /**
     * Get service IDs that a user participated in (for CV / reporting)
     */
    public function getServiceIdsByUserUid(int $userUid): array
    {
        $rows = $this->select('academic_service_id')
            ->where('user_uid', $userUid)
            ->findAll();
        return array_map('intval', array_column($rows, 'academic_service_id'));
    }

    /**
     * Count participants for a service
     */
    public function countByServiceId(int $serviceId): int
    {
        return (int) $this->where('academic_service_id', $serviceId)->countAllResults();
    }
}
