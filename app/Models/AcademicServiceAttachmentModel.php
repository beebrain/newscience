<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicServiceAttachmentModel extends Model
{
    protected $table            = 'academic_service_attachments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'academic_service_id',
        'original_name',
        'stored_path',
        'file_size',
        'sort_order',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function getByServiceId(int $serviceId): array
    {
        return $this->where('academic_service_id', $serviceId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * @param int[] $serviceIds
     * @return array<int, int> service_id => count
     */
    public function countsByServiceIds(array $serviceIds): array
    {
        if ($serviceIds === []) {
            return [];
        }
        $rows = $this->select('academic_service_id, COUNT(*) as c')
            ->whereIn('academic_service_id', $serviceIds)
            ->groupBy('academic_service_id')
            ->findAll();
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['academic_service_id']] = (int) $row['c'];
        }
        return $out;
    }

    public static function serveUrl(array $row): string
    {
        $p = $row['stored_path'] ?? '';
        if ($p === '') {
            return '';
        }

        return base_url('serve/uploads/' . str_replace('\\', '/', $p));
    }

    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $i     = 0;
        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
