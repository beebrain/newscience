<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicServiceModel extends Model
{
    protected $table            = 'academic_services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'academic_year',
        'service_date',
        'service_date_end',
        'title',
        'project_owner_type',
        'project_owner_spec',
        'venue_type',
        'venue_spec',
        'target_group_type',
        'target_group_spec',
        'responsible_type',
        'responsible_program',
        'responsible_person_text',
        'service_type',
        'service_type_spec',
        'budget_source',
        'budget_source_spec',
        'has_compensation',
        'compensation_amount',
        'revenue_amount',
        'revenue_unknown',
        'created_by_uid',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get services filtered by academic year (พ.ศ.)
     */
    public function getByYear(?string $year): array
    {
        $builder = $this->orderBy('service_date', 'DESC');
        if ($year !== null && $year !== '') {
            $builder->where('academic_year', $year);
        }
        return $builder->findAll();
    }

    /**
     * Search by keyword (title), optional year, optional service_date range
     */
    public function search(?string $keyword, ?string $year = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $builder = $this->orderBy('service_date', 'DESC');
        if ($keyword !== null && $keyword !== '') {
            $builder->like('title', $keyword);
        }
        if ($year !== null && $year !== '') {
            $builder->where('academic_year', $year);
        }
        // ช่วงกิจกรรมทับซ้อนกับตัวกรอง [dateFrom, dateTo]
        if ($dateFrom !== null && $dateFrom !== '') {
            $builder->where(
                'COALESCE(service_date_end, service_date) >= ' . $this->db->escape($dateFrom),
                null,
                false
            );
        }
        if ($dateTo !== null && $dateTo !== '') {
            $builder->where(
                'service_date <= ' . $this->db->escape($dateTo),
                null,
                false
            );
        }
        return $builder->findAll();
    }

    /**
     * Get one service with its participants (joined)
     */
    public function getWithParticipants(int $id): ?array
    {
        $service = $this->find($id);
        if ($service === null) {
            return null;
        }
        $participantModel = model(AcademicServiceParticipantModel::class);
        $service['participants'] = $participantModel->getByServiceId($id);
        if (\Config\Database::connect()->tableExists('academic_service_attachments')) {
            $attachmentModel         = model(AcademicServiceAttachmentModel::class);
            $service['attachments'] = $attachmentModel->getByServiceId($id);
        } else {
            $service['attachments'] = [];
        }
        return $service;
    }

    /**
     * Get list of academic years that have data (for filter dropdown)
     */
    public function getDistinctYears(): array
    {
        $rows = $this->select('academic_year')
            ->distinct()
            ->where('academic_year IS NOT NULL')
            ->where('academic_year !=', '')
            ->orderBy('academic_year', 'DESC')
            ->findAll();
        return array_column($rows, 'academic_year');
    }
}
