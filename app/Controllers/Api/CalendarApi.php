<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\AccessControl;
use App\Models\CalendarEventModel;
use App\Models\CalendarParticipantModel;
use App\Models\UserModel;

/**
 * CalendarApi - JSON endpoints for FullCalendar feed and CRUD (ปฏิทินนัดหมาย)
 * Requires logged-in user. Admin: full access + tag anyone. User: own events only + tag self only.
 */
class CalendarApi extends BaseController
{
    protected CalendarEventModel $eventModel;
    protected CalendarParticipantModel $participantModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->eventModel       = new CalendarEventModel();
        $this->participantModel = new CalendarParticipantModel();
        $this->userModel        = new UserModel();
    }

    /**
     * GET api/calendar/events?start=...&end=...&user_id=...
     * Returns JSON events for FullCalendar
     */
    public function events()
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');
        $filterUserId = $this->request->getGet('user_id');
        if ($filterUserId !== null && $filterUserId !== '') {
            $filterUserId = (int) $filterUserId;
        } else {
            $filterUserId = null;
        }

        if (! $start || ! $end) {
            return $this->response->setJSON(['error' => 'start and end required'])->setStatusCode(400);
        }

        $isAdmin = $this->isCalendarAdmin($userId);
        if (! $isAdmin && $filterUserId !== null && $filterUserId !== $userId) {
            $filterUserId = $userId;
        }

        $events = $this->eventModel->getEventsForRange($start, $end, $filterUserId);
        return $this->response->setJSON($events);
    }

    /**
     * GET api/calendar/public/events?start=...&end=... — สาธารณะ ไม่ต้องล็อกอิน แสดงทุกกิจกรรม (status active)
     */
    public function publicEvents()
    {
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');
        if (! $start || ! $end) {
            return $this->response->setJSON(['error' => 'start and end required'])->setStatusCode(400);
        }
        $events = $this->eventModel->getEventsForRange($start, $end, null);
        return $this->response->setJSON($events);
    }

    /**
     * GET api/calendar/public/feed — ฟีด .ics สำหรับสมัครรับในแอปปฏิทินมือถือ (ไม่ต้องล็อกอิน)
     * คืนช่วงประมาณ 2 เดือนที่ผ่านมา ถึง 14 เดือนข้างหน้า
     */
    public function publicFeedIcs()
    {
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');
        if (! $start || ! $end) {
            $start = date('Y-m-d 00:00:00', strtotime('-2 months'));
            $end   = date('Y-m-d 23:59:59', strtotime('+14 months'));
        }
        $events = $this->eventModel->getEventsForRange($start, $end, null);
        $ics = $this->buildIcs($events);
        return $this->response
            ->setHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody($ics);
    }

    /**
     * GET api/calendar/users - list users for filter dropdown (admin gets all, user gets self)
     */
    public function users()
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        if ($this->isCalendarAdmin($userId)) {
            $list = $this->userModel->getCalendarEligibleUsers();
        } else {
            $user = $this->userModel->find($userId);
            $list = [];
            if ($user) {
                $list[] = [
                    'uid'     => (int) $user['uid'],
                    'email'   => $user['email'] ?? '',
                    'name_th' => $this->userModel->getFullNameThaiForDisplay($user),
                    'name_en' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')),
                ];
            }
        }
        return $this->response->setJSON($list);
    }

    /**
     * GET api/calendar/export-ics?start=...&end=...&user_id=... - ดาวน์โหลดกิจกรรมเป็นไฟล์ .ics นำเข้าแอปปฏิทินได้
     */
    public function exportIcs()
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');
        if (! $start || ! $end) {
            $start = date('Y-m-01 00:00:00');
            $end   = date('Y-m-t 23:59:59');
        }

        $filterUserId = $this->request->getGet('user_id');
        if ($filterUserId !== null && $filterUserId !== '') {
            $filterUserId = (int) $filterUserId;
        } else {
            $filterUserId = null;
        }

        $isAdmin = $this->isCalendarAdmin($userId);
        if (! $isAdmin && $filterUserId !== null && $filterUserId !== $userId) {
            $filterUserId = $userId;
        }

        $events = $this->eventModel->getEventsForRange($start, $end, $filterUserId);
        $ics = $this->buildIcs($events);
        return $this->response
            ->setHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="calendar.ics"')
            ->setBody($ics);
    }

    /**
     * GET api/calendar/event/(:num) - get one event with participants (for edit modal)
     */
    public function getEvent(int $id)
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $event = $this->eventModel->getWithParticipants($id);
        if (! $event) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $isAdmin = $this->isCalendarAdmin($userId);
        $canEdit = $isAdmin || (int) $event['created_by'] === $userId;
        if (! $canEdit) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $event['participant_emails'] = $this->participantModel->getParticipantEmails($id);
        return $this->response->setJSON($event);
    }

    /**
     * POST api/calendar/store - create event
     */
    public function store()
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(401);
        }

        $isAdmin = $this->isCalendarAdmin($userId);
        $currentUser = $this->userModel->find($userId);
        $currentEmail = $currentUser['email'] ?? '';

        $participantEmails = $this->request->getPost('participants');
        if (is_string($participantEmails)) {
            $participantEmails = $participantEmails !== '' ? array_map('trim', explode(',', $participantEmails)) : [];
        } else {
            $participantEmails = is_array($participantEmails) ? array_map('trim', $participantEmails) : [];
        }
        $participantEmails = array_values(array_filter($participantEmails));
        if (! $isAdmin) {
            $participantEmails = array_intersect($participantEmails, [$currentEmail]);
            if (empty($participantEmails)) {
                $participantEmails = $currentEmail !== '' ? [$currentEmail] : [];
            }
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'title required']);
        }

        $start = $this->request->getPost('start_datetime') ?: $this->request->getPost('start');
        $end   = $this->request->getPost('end_datetime') ?: $this->request->getPost('end');
        if (! $start || ! $end) {
            return $this->response->setJSON(['success' => false, 'error' => 'start and end required']);
        }

        $allDay = (bool) $this->request->getPost('all_day');
        $color  = $this->request->getPost('color') ?: '#3b82f6';

        $data = [
            'title'          => $title,
            'description'    => $this->request->getPost('description'),
            'start_datetime' => $start,
            'end_datetime'   => $end,
            'all_day'        => $allDay ? 1 : 0,
            'location'       => $this->request->getPost('location'),
            'color'          => $color,
            'status'         => 'active',
            'created_by'     => $userId,
        ];

        $eventId = $this->eventModel->insert($data);
        if (! $eventId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Insert failed']);
        }

        $this->participantModel->syncParticipants($eventId, $participantEmails, $userId);
        $event = $this->eventModel->find($eventId);
        $out = $this->eventToFullCalendar($event);
        $out['id'] = (int) $eventId;
        return $this->response->setJSON(['success' => true, 'event' => $out]);
    }

    /**
     * POST api/calendar/update/(:num)
     */
    public function update(int $id)
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(401);
        }

        $event = $this->eventModel->find($id);
        if (! $event) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found'])->setStatusCode(404);
        }

        $isAdmin = $this->isCalendarAdmin($userId);
        $canEdit = $isAdmin || (int) $event['created_by'] === $userId;
        if (! $canEdit) {
            return $this->response->setJSON(['success' => false, 'error' => 'Forbidden'])->setStatusCode(403);
        }

        $currentUser = $this->userModel->find($userId);
        $currentEmail = $currentUser['email'] ?? '';

        $participantEmails = $this->request->getPost('participants');
        $syncParticipants = $participantEmails !== null;
        if ($syncParticipants) {
            if (is_string($participantEmails)) {
                $participantEmails = $participantEmails !== '' ? array_map('trim', explode(',', $participantEmails)) : [];
            } else {
                $participantEmails = is_array($participantEmails) ? array_map('trim', $participantEmails) : [];
            }
            $participantEmails = array_values(array_filter($participantEmails));
            if (! $isAdmin) {
                $participantEmails = array_intersect($participantEmails, [$currentEmail]);
                if (empty($participantEmails)) {
                    $participantEmails = $currentEmail !== '' ? [$currentEmail] : [];
                }
            }
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'title required']);
        }

        $start = $this->request->getPost('start_datetime') ?: $this->request->getPost('start');
        $end   = $this->request->getPost('end_datetime') ?: $this->request->getPost('end');
        if (! $start || ! $end) {
            return $this->response->setJSON(['success' => false, 'error' => 'start and end required']);
        }

        $data = [
            'title'          => $title,
            'description'    => $this->request->getPost('description'),
            'start_datetime' => $start,
            'end_datetime'   => $end,
            'all_day'        => (bool) $this->request->getPost('all_day') ? 1 : 0,
            'location'       => $this->request->getPost('location'),
            'color'          => $this->request->getPost('color') ?: $event['color'],
        ];

        $this->eventModel->update($id, $data);
        if ($syncParticipants) {
            $this->participantModel->syncParticipants($id, $participantEmails, $userId);
        }

        $updated = $this->eventModel->getEventsForRange($start, $end, null);
        $out = null;
        foreach ($updated as $ev) {
            if ((int) $ev['id'] === $id) {
                $out = $ev;
                break;
            }
        }
        if (! $out) {
            $out = $this->eventToFullCalendar($this->eventModel->find($id));
            $out['id'] = $id;
        }
        return $this->response->setJSON(['success' => true, 'event' => $out]);
    }

    /**
     * POST api/calendar/delete/(:num)
     */
    public function delete(int $id)
    {
        $userId = (int) session()->get('admin_id');
        if (! $userId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(401);
        }

        $event = $this->eventModel->find($id);
        if (! $event) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found'])->setStatusCode(404);
        }

        $isAdmin = $this->isCalendarAdmin($userId);
        $canEdit = $isAdmin || (int) $event['created_by'] === $userId;
        if (! $canEdit) {
            return $this->response->setJSON(['success' => false, 'error' => 'Forbidden'])->setStatusCode(403);
        }

        $this->eventModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * สร้างเนื้อหา iCalendar (.ics) จากรายการ events (รูปแบบจาก getEventsForRange)
     */
    private function buildIcs(array $events): string
    {
        $prodId = '-//Science Calendar//TH';
        $lines  = ["BEGIN:VCALENDAR", "VERSION:2.0", "PRODID:{$prodId}", "CALSCALE:GREGORIAN"];
        $domain = parse_url(base_url(), PHP_URL_HOST) ?: 'calendar';

        foreach ($events as $ev) {
            $uid    = 'event-' . ($ev['id'] ?? uniqid()) . '@' . $domain;
            $title  = $ev['title'] ?? 'Event';
            $desc   = $ev['extendedProps']['description'] ?? '';
            $loc    = $ev['extendedProps']['location'] ?? '';
            $allDay = ! empty($ev['allDay']);
            $start  = $ev['start'] ?? '';
            $end    = $ev['end'] ?? '';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = $this->icsFold('SUMMARY:' . $this->icsEscape($title));

            if ($desc !== '') {
                $lines[] = $this->icsFold('DESCRIPTION:' . $this->icsEscape($desc));
            }
            if ($loc !== '') {
                $lines[] = $this->icsFold('LOCATION:' . $this->icsEscape($loc));
            }

            if ($allDay) {
                $dtStart = substr(str_replace(['-', ' ', ':'], '', $start), 0, 8);
                $dtEnd   = substr(str_replace(['-', ' ', ':'], '', $end), 0, 8);
                $lines[] = 'DTSTART;VALUE=DATE:' . $dtStart;
                $lines[] = 'DTEND;VALUE=DATE:' . $dtEnd;
            } else {
                $tsStart = strtotime($start);
                $tsEnd   = strtotime($end);
                $lines[] = 'DTSTART:' . ($tsStart ? gmdate('Ymd\THis\Z', $tsStart) : '');
                $lines[] = 'DTEND:' . ($tsEnd ? gmdate('Ymd\THis\Z', $tsEnd) : '');
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    private function icsEscape(string $s): string
    {
        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $s);
    }

    private function icsFold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $out = substr($line, 0, 75);
        $i   = 75;
        while ($i < strlen($line)) {
            $out .= "\r\n " . substr($line, $i, 74);
            $i   += 74;
        }
        return $out;
    }

    private function isCalendarAdmin(int $uid): bool
    {
        $role = session()->get('admin_role');
        if (in_array($role, ['super_admin', 'faculty_admin', 'admin', 'editor'], true)) {
            return true;
        }
        return AccessControl::hasAccess($uid, 'calendar');
    }

    private function eventToFullCalendar(array $event): array
    {
        return [
            'id'     => (int) $event['id'],
            'title'  => $event['title'],
            'start'  => $event['start_datetime'],
            'end'    => $event['end_datetime'],
            'allDay' => (bool) ($event['all_day'] ?? 0),
            'color'  => $event['color'] ?? '#3b82f6',
            'extendedProps' => [
                'description' => $event['description'] ?? '',
                'location'    => $event['location'] ?? '',
                'created_by'  => (int) ($event['created_by'] ?? 0),
            ],
        ];
    }
}
