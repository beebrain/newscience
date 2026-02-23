<?php

namespace App\Models;

use CodeIgniter\Model;

class CertApprovalModel extends Model
{
    protected $table = 'cert_approvals';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'request_id',
        'action',
        'actor_id',
        'actor_role',
        'comment',
        'ip_address',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function log(int $requestId, string $action, int $actorUid, ?string $role = null, ?string $comment = null, ?string $ip = null): bool
    {
        return (bool) $this->insert([
            'request_id' => $requestId,
            'action'     => $action,
            'actor_id'   => $actorUid,
            'actor_role' => $role,
            'comment'    => $comment,
            'ip_address' => $ip,
        ]);
    }

    public function getTimeline(int $requestId): array
    {
        return $this->where('request_id', $requestId)
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }
}
