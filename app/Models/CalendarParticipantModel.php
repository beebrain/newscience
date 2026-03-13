<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CalendarParticipantModel - ผู้เข้าร่วมกิจกรรม (Tag) ในตารางนัดหมาย (Feature: Calendar)
 * เก็บ user_email แทน user_id
 */
class CalendarParticipantModel extends Model
{
    protected $table            = 'calendar_participants';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields     = [
        'event_id',
        'user_email',
        'added_by',
    ];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * ดึงรายการ user ที่เป็น participant ของหลาย event (สำหรับ feed)
     * Join กับ user ผ่าน email
     * @return array<int, array> event_id => [ user rows ]
     */
    public function getParticipantsByEventIds(array $eventIds): array
    {
        if (empty($eventIds)) {
            return [];
        }
        $ids = array_map('intval', $eventIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->query(
            "SELECT cp.event_id, cp.user_email, u.uid, u.email, u.gf_name, u.gl_name, u.tf_name, u.tl_name, u.title
             FROM {$this->table} cp
             LEFT JOIN user u ON u.email = cp.user_email
             WHERE cp.event_id IN ({$placeholders})",
            $ids
        )->getResultArray();

        $byEvent = [];
        foreach ($rows as $row) {
            $eid = (int) $row['event_id'];
            if (! isset($byEvent[$eid])) {
                $byEvent[$eid] = [];
            }
            $byEvent[$eid][] = $row;
        }
        return $byEvent;
    }

    /**
     * ดึงรายการ participants (user rows) ของ event เดียว (join ผ่าน email)
     */
    public function getParticipantsForEvent(int $eventId): array
    {
        $rows = $this->db->table($this->table . ' cp')
            ->select('cp.user_email, u.uid, u.email, u.gf_name, u.gl_name, u.tf_name, u.tl_name, u.title')
            ->join('user u', 'u.email = cp.user_email', 'left')
            ->where('cp.event_id', $eventId)
            ->get()
            ->getResultArray();
        return $rows;
    }

    /**
     * ดึง user_email ทั้งหมดของ event
     * @return string[]
     */
    public function getParticipantEmails(int $eventId): array
    {
        $rows = $this->where('event_id', $eventId)->findAll();
        return array_map(static fn ($r) => (string) $r['user_email'], $rows);
    }

    /**
     * เพิ่ม participants (ไม่ลบของเดิม) - รับอีเมล
     */
    public function addParticipants(int $eventId, array $emails, ?int $addedBy = null): void
    {
        foreach ($emails as $email) {
            $email = trim((string) $email);
            if ($email === '') {
                continue;
            }
            $exists = $this->where('event_id', $eventId)->where('user_email', $email)->first();
            if (! $exists) {
                $this->insert([
                    'event_id'   => $eventId,
                    'user_email' => $email,
                    'added_by'   => $addedBy,
                ]);
            }
        }
    }

    /**
     * sync participants: ลบทั้งหมดแล้วเพิ่มตาม emails
     */
    public function syncParticipants(int $eventId, array $emails, ?int $addedBy = null): void
    {
        $this->where('event_id', $eventId)->delete();
        $emails = array_unique(array_map(static fn ($e) => trim((string) $e), $emails));
        foreach ($emails as $email) {
            if ($email === '') {
                continue;
            }
            $this->insert([
                'event_id'   => $eventId,
                'user_email' => $email,
                'added_by'   => $addedBy,
            ]);
        }
    }
}
