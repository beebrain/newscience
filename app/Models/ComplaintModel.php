<?php

namespace App\Models;

use CodeIgniter\Model;

class ComplaintModel extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    protected $table = 'complaints';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'complainant_name',
        'complainant_email',
        'complainant_phone',
        'subject',
        'detail',
        'attachment_path',
        'status',
        'ip_address',
        'user_agent',
    ];

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_NEW => 'ใหม่',
            self::STATUS_IN_PROGRESS => 'กำลังดำเนินการ',
            self::STATUS_CLOSED => 'ปิดเรื่อง',
        ];
    }

    public static function isValidStatus(?string $status): bool
    {
        return $status !== null && array_key_exists($status, self::getStatusOptions());
    }

    public function createComplaint(array $data): int
    {
        $data['status'] = $data['status'] ?? self::STATUS_NEW;
        $this->insert($data);

        return (int) $this->getInsertID();
    }
}
