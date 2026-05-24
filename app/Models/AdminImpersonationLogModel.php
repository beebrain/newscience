<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminImpersonationLogModel extends Model
{
    protected $table         = 'admin_impersonation_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'actor_uid',
        'actor_email',
        'target_uid',
        'target_email',
        'target_role',
        'event',
        'reason',
        'status',
        'ip_address',
        'user_agent',
        'session_id_hash',
        'context_json',
        'started_at',
        'ended_at',
        'ended_by',
    ];

    public function logEvent(string $event, array $data): int
    {
        $data['event'] = $event;
        if (isset($data['context']) && is_array($data['context'])) {
            $data['context_json'] = json_encode($data['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            unset($data['context']);
        }

        $id = $this->insert($data);
        return $id ? (int) $id : 0;
    }

    public function close(int $id, string $endedBy, string $status = 'ended'): bool
    {
        return (bool) $this->update($id, [
            'status'   => $status,
            'ended_at' => date('Y-m-d H:i:s'),
            'ended_by' => $endedBy,
        ]);
    }
}
