<?php

namespace App\Models;

use CodeIgniter\Model;

class BarcodeEventEligibleModel extends Model
{
    protected $table = 'barcode_event_eligibles';
    protected $primaryKey = ['barcode_event_id', 'student_user_id'];
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'barcode_event_id',
        'student_user_id',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    /**
     * Composite primary key: (barcode_event_id, student_user_id)
     * CodeIgniter expects single PK; we use $useAutoIncrement = false and handle manually.
     */
    public function addEligible(int $barcodeEventId, int $studentUserId): bool
    {
        $exists = $this->where('barcode_event_id', $barcodeEventId)
            ->where('student_user_id', $studentUserId)
            ->first();
        if ($exists) {
            return true;
        }
        return $this->db->table($this->table)->insert([
            'barcode_event_id' => $barcodeEventId,
            'student_user_id' => $studentUserId,
        ]);
    }

    public function removeEligible(int $barcodeEventId, int $studentUserId): bool
    {
        return $this->db->table($this->table)
            ->where('barcode_event_id', $barcodeEventId)
            ->where('student_user_id', $studentUserId)
            ->delete();
    }

    /**
     * Get eligible student_user ids for an event
     */
    public function getEligibleStudentIds(int $barcodeEventId): array
    {
        $rows = $this->where('barcode_event_id', $barcodeEventId)->findAll();
        return array_map('intval', array_column($rows, 'student_user_id'));
    }

    /**
     * Get eligibles with student_user details
     */
    public function getEligiblesWithStudents(int $barcodeEventId): array
    {
        $studentModel = new StudentUserModel();
        $rows = $this->where('barcode_event_id', $barcodeEventId)->findAll();
        $out = [];
        foreach ($rows as $r) {
            $student = $studentModel->find($r['student_user_id']);
            $out[] = [
                'barcode_event_id' => (int) $r['barcode_event_id'],
                'student_user_id' => (int) $r['student_user_id'],
                'created_at' => $r['created_at'] ?? null,
                'student' => $student,
                'student_display_name' => $student ? $studentModel->getFullName($student) : '',
            ];
        }
        return $out;
    }

    /**
     * Check if student is eligible for this event
     */
    public function isEligible(int $barcodeEventId, int $studentUserId): bool
    {
        return $this->where('barcode_event_id', $barcodeEventId)
            ->where('student_user_id', $studentUserId)
            ->first() !== null;
    }

    /**
     * Get barcode_event_id list where this student is eligible
     */
    public function getEventIdsWhereEligible(int $studentUserId): array
    {
        $rows = $this->where('student_user_id', $studentUserId)->findAll();
        return array_map('intval', array_column($rows, 'barcode_event_id'));
    }
}
