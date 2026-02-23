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
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * ดึงกิจกรรมพร้อมข้อมูล template และผู้ลงนาม
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->select('cert_events.*, cert_templates.name_th as template_name, cert_templates.name_en as template_name_en, cert_templates.template_file, cert_templates.field_mapping, cert_templates.signature_x, cert_templates.signature_y, cert_templates.qr_x, cert_templates.qr_y, cert_templates.qr_size, user.thai_name as signer_name, user.thai_lastname as signer_lastname')
            ->join('cert_templates', 'cert_templates.id = cert_events.template_id', 'left')
            ->join('user', 'user.uid = cert_events.signer_id', 'left')
            ->where('cert_events.id', $id)
            ->first();
    }

    /**
     * ดึงรายการกิจกรรมทั้งหมดพร้อมสถิติ
     */
    public function getAllWithStats(?string $status = null, int $limit = 50): array
    {
        $builder = $this->select('cert_events.*, cert_templates.name_th as template_name, 
            COUNT(cer.id) as total_recipients,
            SUM(CASE WHEN cer.status = "issued" THEN 1 ELSE 0 END) as issued_count,
            SUM(CASE WHEN cer.status = "pending" THEN 1 ELSE 0 END) as pending_count')
            ->join('cert_templates', 'cert_templates.id = cert_events.template_id', 'left')
            ->join('cert_event_recipients cer', 'cer.event_id = cert_events.id', 'left')
            ->groupBy('cert_events.id');

        if ($status) {
            $builder->where('cert_events.status', $status);
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
     * อัพเดทสถานะกิจกรรม
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
}
