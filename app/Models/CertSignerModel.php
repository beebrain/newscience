<?php

namespace App\Models;

use CodeIgniter\Model;

class CertSignerModel extends Model
{
    protected $table = 'cert_signers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_uid',
        'signer_role',
        'program_id',
        'signature_image',
        'pfx_path',
        'pfx_password_enc',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActiveSigners(string $role, ?int $programId = null): array
    {
        $builder = $this->where('signer_role', $role)
                        ->where('is_active', 1);
        if ($programId !== null) {
            $builder->groupStart()
                    ->where('program_id', $programId)
                    ->orWhere('program_id', null)
                    ->groupEnd();
        }

        return $builder->findAll();
    }

    public function findSignerForRequest(string $role, ?int $programId = null): ?array
    {
        $builder = $this->where('signer_role', $role)
                        ->where('is_active', 1)
                        ->orderBy('program_id', 'DESC');
        if ($programId !== null) {
            $builder->groupStart()
                    ->where('program_id', $programId)
                    ->orWhere('program_id', null)
                    ->groupEnd();
        }
        return $builder->first();
    }
}
