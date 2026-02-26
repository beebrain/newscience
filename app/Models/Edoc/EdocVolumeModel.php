<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdocVolumeModel extends Model
{
    protected $table = 'edoc_volumes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'year',
        'volume_type',
        'volume_label',
        'is_active',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Volume type labels (Thai)
     */
    const VOLUME_TYPES = [
        'send_internal'    => 'ส่งภายใน',
        'receive_internal' => 'รับภายใน',
        'external'         => 'ภายนอก',
        'order'            => 'คำสั่ง',
        'announcement'     => 'ประกาศ',
    ];

    /**
     * Create all 5 volumes for a given calendar year
     *
     * @param int $year Calendar year (e.g. 2569)
     * @param int|null $createdBy Admin user ID
     * @return array Created volumes
     */
    public function createYearVolumes(int $year, ?int $createdBy = null): array
    {
        $created = [];

        foreach (self::VOLUME_TYPES as $type => $label) {
            // Skip if already exists
            $exists = $this->where('year', $year)
                ->where('volume_type', $type)
                ->first();

            if ($exists) {
                $created[] = $exists;
                continue;
            }

            $data = [
                'year'         => $year,
                'volume_type'  => $type,
                'volume_label' => $label . ' ' . $year,
                'is_active'    => 1,
                'created_by'   => $createdBy,
            ];

            $id = $this->insert($data);
            if ($id) {
                $created[] = $this->find($id);
            }
        }

        return $created;
    }

    /**
     * Get all volumes for a given year
     *
     * @param int $year
     * @return array
     */
    public function getByYear(int $year): array
    {
        return $this->where('year', $year)
            ->orderBy("FIELD(volume_type, 'send_internal','receive_internal','external','order','announcement')")
            ->findAll();
    }

    /**
     * Get all distinct years that have volumes
     *
     * @return array
     */
    public function getAvailableYears(): array
    {
        $result = $this->select('year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->findAll();

        return array_column($result, 'year');
    }

    /**
     * Get active volumes for a year
     *
     * @param int $year
     * @return array
     */
    public function getActiveByYear(int $year): array
    {
        return $this->where('year', $year)
            ->where('is_active', 1)
            ->orderBy("FIELD(volume_type, 'send_internal','receive_internal','external','order','announcement')")
            ->findAll();
    }

    /**
     * Toggle volume active status
     *
     * @param int $id
     * @return bool
     */
    public function toggleActive(int $id): bool
    {
        $volume = $this->find($id);
        if (!$volume) {
            return false;
        }

        return $this->update($id, [
            'is_active' => $volume['is_active'] ? 0 : 1,
        ]);
    }

    /**
     * Get volume type label in Thai
     *
     * @param string $type
     * @return string
     */
    public static function getTypeLabel(string $type): string
    {
        return self::VOLUME_TYPES[$type] ?? $type;
    }

    /**
     * Get document count per volume
     *
     * @param int $year
     * @return array
     */
    public function getVolumeDocCounts(int $year): array
    {
        $volumes = $this->getByYear($year);
        $result = [];

        foreach ($volumes as $vol) {
            $count = $this->db->table('edoctitle')
                ->where('volume_id', $vol['id'])
                ->countAllResults();
            $vol['doc_count'] = $count;
            $result[] = $vol;
        }

        return $result;
    }
}
