<?php

namespace App\Models;

use CodeIgniter\Model;

class BarcodeModel extends Model
{
    protected $table = 'barcodes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'barcode_event_id',
        'code',
        'student_user_id',
        'assigned_at',
        'claimed_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null; // ตาราง barcodes ไม่มี updated_at

    protected $validationRules = [
        'barcode_event_id' => 'required|integer',
        'code' => 'required|max_length[255]',
    ];

    /**
     * Get barcodes for an event (optionally only unassigned)
     */
    public function getByEvent(int $barcodeEventId, bool $unassignedOnly = false): array
    {
        $builder = $this->where('barcode_event_id', $barcodeEventId);
        if ($unassignedOnly) {
            $builder->where('student_user_id IS NULL');
        }
        return $builder->orderBy('id', 'ASC')->findAll();
    }

    /**
     * Get barcodes assigned to a student (for portal display)
     */
    public function getByStudentUser(int $studentUserId): array
    {
        return $this->where('student_user_id', $studentUserId)
            ->orderBy('assigned_at', 'DESC')
            ->findAll();
    }

    /**
     * Assign a barcode to a student
     */
    public function assignToStudent(int $barcodeId, int $studentUserId): bool
    {
        return $this->update($barcodeId, [
            'student_user_id' => $studentUserId,
            'assigned_at' => date('Y-m-d H:i:s'),
            'claimed_at' => null,
        ]);
    }

    /**
     * Unassign barcode from student (set student_user_id and assigned_at to NULL; clear claimed_at)
     */
    public function unassignFromStudent(int $barcodeId): bool
    {
        return $this->update($barcodeId, [
            'student_user_id' => null,
            'assigned_at' => null,
            'claimed_at' => null,
        ]);
    }

    /**
     * Student claims (confirms receipt of) barcode — sets claimed_at. Only if barcode is assigned to this student and not yet claimed.
     */
    public function claimByStudent(int $barcodeId, int $studentUserId): bool
    {
        $row = $this->find($barcodeId);
        if (!$row || (int) $row['student_user_id'] !== $studentUserId) {
            return false;
        }
        if (!empty($row['claimed_at'])) {
            return true; // already claimed
        }
        return $this->update($barcodeId, [
            'claimed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete a barcode by id; returns true if deleted (and belonged to given event)
     */
    public function deleteBarcode(int $barcodeId, int $barcodeEventId): bool
    {
        $row = $this->find($barcodeId);
        if (!$row || (int) $row['barcode_event_id'] !== $barcodeEventId) {
            return false;
        }
        return $this->delete($barcodeId) !== false;
    }

    /**
     * Bulk insert codes for an event (from N8n JSON)
     * ใช้ builder โดยตรงเพื่อให้คอลัมน์ชัดเจน (ตารางมีแค่ created_at ไม่มี updated_at)
     * @param int $barcodeEventId
     * @param string[] $codes
     * @return array{inserted: int, skipped: int, errors: string[]}
     */
    public function bulkInsertCodes(int $barcodeEventId, array $codes): array
    {
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        $now = date('Y-m-d H:i:s');
        foreach ($codes as $code) {
            $code = is_string($code) ? trim($code) : (string) $code;
            if ($code === '') {
                continue;
            }
            $exists = $this->where('barcode_event_id', $barcodeEventId)->where('code', $code)->first();
            if ($exists) {
                $skipped++;
                continue;
            }
            try {
                $this->db->table($this->table)->insert([
                    'barcode_event_id' => $barcodeEventId,
                    'code' => $code,
                    'created_at' => $now,
                ]);
                $inserted++;
            } catch (\Throwable $e) {
                $errors[] = $code . ': ' . $e->getMessage();
            }
        }
        return ['inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors];
    }
}
