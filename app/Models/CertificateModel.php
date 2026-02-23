<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificateModel extends Model
{
    protected $table = 'certificates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'request_id',
        'certificate_no',
        'pdf_path',
        'pdf_hash',
        'verification_token',
        'student_snapshot',
        'signed_by',
        'signed_at',
        'download_count',
        'last_downloaded_at',
        'issued_date',
        'expiry_date',
        'is_revoked',
        'revoked_reason',
        'revoked_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function findByToken(string $token): ?array
    {
        return $this->where('verification_token', $token)->first();
    }
}
