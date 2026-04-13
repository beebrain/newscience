<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CertEventModel - จัดการกิจกรรม/หัวข้ออบรมที่จะออก Certificate
 */
class CertEventModel extends Model
{
    protected $table = 'cert_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'description',
        'event_date',
        'template_id',
        'signer_id',
        'status',
        'created_by',
        'background_file',
        'background_kind',
        'layout_json',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * ดึงกิจกรรมพร้อมผู้ลงนาม
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->select('cert_events.*, user.tf_name as signer_name, user.tl_name as signer_lastname')
            ->join('user', 'user.uid = cert_events.signer_id', 'left')
            ->where('cert_events.id', $id)
            ->first();
    }

    /**
     * ดึงรายการกิจกรรมทั้งหมดพร้อมสถิติ
     */
    public function getAllWithStats(?string $status = null, int $limit = 50, ?int $createdBy = null): array
    {
        $builder = $this->select('cert_events.*,
            MAX(u.tf_name) as creator_tf_name,
            MAX(u.tl_name) as creator_tl_name,
            MAX(u.email) as creator_email,
            COUNT(cer.id) as total_recipients,
            SUM(CASE WHEN cer.status = "issued" THEN 1 ELSE 0 END) as issued_count,
            SUM(CASE WHEN cer.status = "pending" THEN 1 ELSE 0 END) as pending_count')
            ->join('user u', 'u.uid = cert_events.created_by', 'left')
            ->join('cert_event_recipients cer', 'cer.event_id = cert_events.id', 'left')
            ->groupBy('cert_events.id');

        if ($status) {
            $builder->where('cert_events.status', $status);
        }
        if ($createdBy !== null && $createdBy > 0) {
            $builder->where('cert_events.created_by', $createdBy);
        }

        return $builder->orderBy('cert_events.created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * ดึงกิจกรรมที่เปิดให้ออก Certificate
     */
    public function getOpenEvents(): array
    {
        return $this->whereIn('status', ['open', 'issued'])
            ->orderBy('event_date', 'DESC')
            ->findAll();
    }

    /**
     * อัปเดตสถานะกิจกรรม
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
}
