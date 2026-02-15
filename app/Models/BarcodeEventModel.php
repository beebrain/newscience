<?php

namespace App\Models;

use CodeIgniter\Model;

class BarcodeEventModel extends Model
{
    protected $table = 'barcode_events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'description',
        'event_date',
        'status',
        'created_by_student_user_id',
        'created_by_user_uid',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title' => 'required|min_length[1]|max_length[500]',
        'event_date' => 'required|valid_date',
    ];

    /**
     * Get all events ordered by event_date desc
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('event_date', 'DESC')->orderBy('id', 'DESC')->findAll();
    }

    /**
     * Get event with counts: total barcodes, assigned count, claimed count, eligibles count
     */
    public function getWithCounts(int $id): ?array
    {
        $event = $this->find($id);
        if (!$event) {
            return null;
        }
        $barcodeModel = new BarcodeModel();
        $eligibleModel = new BarcodeEventEligibleModel();
        $event['barcode_total'] = $barcodeModel->where('barcode_event_id', $id)->countAllResults();
        $event['barcode_assigned'] = (new BarcodeModel())->where('barcode_event_id', $id)->where('student_user_id IS NOT NULL')->countAllResults();
        try {
            $event['barcode_claimed'] = (new BarcodeModel())->where('barcode_event_id', $id)->where('claimed_at IS NOT NULL')->countAllResults();
        } catch (\Throwable $e) {
            $event['barcode_claimed'] = 0;
        }
        $event['eligibles_count'] = $eligibleModel->where('barcode_event_id', $id)->countAllResults();
        return $event;
    }
}
