<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CalendarEventModel - ตารางนัดหมายกิจกรรมผู้บริหาร (Feature: Calendar)
 */
class CalendarEventModel extends Model
{
    protected $table            = 'calendar_events';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields     = [
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'all_day',
        'location',
        'color',
        'status',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * ดึงกิจกรรมในช่วงวันที่ (สำหรับ FullCalendar feed)
     * ถ้า userId กำหนด = แสดงเฉพาะกิจกรรมที่ user นั้นเป็น participant (เทียบด้วย email)
     *
     * @param string $start Y-m-d H:i:s
     * @param string $end   Y-m-d H:i:s
     * @param int|null $userId filter by participant uid (null = all) — ใช้ email ของ user นี้ filter
     * @return array<array> events with id, title, start, end, allDay, color, extendedProps
     */
    public function getEventsForRange(string $start, string $end, ?int $userId = null): array
    {
        $userEmail = null;
        if ($userId !== null) {
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $userEmail = $user['email'] ?? null;
        }

        $builder = $this->db->table($this->table . ' e')
            ->select('e.id, e.title, e.description, e.start_datetime, e.end_datetime, e.all_day, e.location, e.color, e.status, e.created_by')
            ->where('e.status', 'active')
            ->groupStart()
                ->where('e.start_datetime >=', $start)
                ->where('e.start_datetime <', $end)
            ->groupEnd();

        if ($userEmail !== null && $userEmail !== '') {
            $builder->join('calendar_participants cp', 'cp.event_id = e.id AND cp.user_email = ' . $this->db->escape($userEmail));
        }

        $rows = $builder->get()->getResultArray();
        $eventIds = array_column($rows, 'id');
        $participantsByEvent = [];
        if (! empty($eventIds)) {
            $participantModel = new CalendarParticipantModel();
            $participantsByEvent = $participantModel->getParticipantsByEventIds($eventIds);
        }

        $userModel = new UserModel();
        $out = [];
        foreach ($rows as $row) {
            $participants = $participantsByEvent[$row['id']] ?? [];
            $participantNames = array_map(static function ($p) use ($userModel) {
                $name = $userModel->getFullNameThaiForDisplay($p);
                return $name !== '' ? $name : ($p['user_email'] ?? $p['email'] ?? '');
            }, $participants);
            $participantEmails = array_map(static fn ($p) => $p['user_email'] ?? $p['email'] ?? '', $participants);
            $out[] = [
                'id'          => (int) $row['id'],
                'title'       => $row['title'],
                'start'       => $row['start_datetime'],
                'end'         => $row['end_datetime'],
                'allDay'      => (bool) ($row['all_day'] ?? 0),
                'color'       => $row['color'] ?? '#3b82f6',
                'extendedProps' => [
                    'description'      => $row['description'],
                    'location'         => $row['location'],
                    'created_by'       => (int) $row['created_by'],
                    'participants'     => $participantNames,
                    'participant_emails' => array_values(array_filter($participantEmails)),
                ],
            ];
        }
        return $out;
    }

    /**
     * ดึงกิจกรรมหนึ่งรายการพร้อม participants (user rows)
     */
    public function getWithParticipants(int $id): ?array
    {
        $event = $this->find($id);
        if (! $event) {
            return null;
        }
        $participantModel = new CalendarParticipantModel();
        $event['participants'] = $participantModel->getParticipantsForEvent($id);
        return $event;
    }
}
