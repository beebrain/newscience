<?php

namespace App\Models;

use CodeIgniter\Model;

class SwParticipantModel extends Model
{
    protected $table      = 'sw_participants';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'registration_id',
        'full_name',
        'level_class',
        'role',
        'game_id',
        'age',
        'occupation',
        'line_id',
        'sort_order',
    ];

    /**
     * ดึง participants ทั้งหมดของ registration นี้ เรียงตาม role (main ก่อน) แล้วตาม sort_order
     */
    public function getByRegistration(int $registrationId): array
    {
        return $this->where('registration_id', $registrationId)
                    ->orderBy('FIELD(role, "main", "reserve")', '', false)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * ดึง participants ของหลาย registration พร้อมกัน (ลด query)
     * คืน [registration_id => [participants]]
     */
    public function getGroupedByRegistrations(array $registrationIds): array
    {
        if (empty($registrationIds)) {
            return [];
        }
        $rows = $this->whereIn('registration_id', $registrationIds)
                     ->orderBy('registration_id')
                     ->orderBy('FIELD(role, "main", "reserve")', '', false)
                     ->orderBy('sort_order', 'ASC')
                     ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['registration_id']][] = $row;
        }
        return $grouped;
    }
}
