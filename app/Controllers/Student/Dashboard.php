<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\BarcodeModel;
use App\Models\BarcodeEventModel;
use App\Models\BarcodeEventEligibleModel;
use App\Models\EventModel;

/**
 * Student Portal: hub (index) + บาร์โค้ด, ข่าว/Event
 */
class Dashboard extends BaseController
{
    /**
     * Portal หลัก — หน้า hub แสดงไอคอนเข้าแต่ละฟีเจอร์
     */
    public function index()
    {
        if (!(int) session()->get('student_id')) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }
        $data = [
            'page_title' => 'Portal นักศึกษา',
        ];
        return view('student/dashboard/index', $data);
    }

    /**
     * บาร์โค้ดของฉัน — Event ที่มีสิทธิ์แต่ยังไม่มีบาร์โค้ด (ปุ่มรับ) + Event ที่มีบาร์โค้ดแล้ว (รหัสหรือกดรับเพื่อดู)
     */
    public function barcodes()
    {
        $studentId = (int) session()->get('student_id');
        if (!$studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $barcodeModel = new BarcodeModel();
        $eventModel = new BarcodeEventModel();
        $eligibleModel = new BarcodeEventEligibleModel();

        $barcodes = $barcodeModel->getByStudentUser($studentId);
        $byEvent = [];
        foreach ($barcodes as $b) {
            $eid = (int) $b['barcode_event_id'];
            if (!isset($byEvent[$eid])) {
                $ev = $eventModel->find($eid);
                $byEvent[$eid] = [
                    'event' => $ev,
                    'event_title' => $ev['title'] ?? 'Event #' . $eid,
                    'event_date' => $ev['event_date'] ?? null,
                    'barcodes' => [],
                ];
            }
            $byEvent[$eid]['barcodes'][] = $b;
        }

        $eligibleEventIds = $eligibleModel->getEventIdsWhereEligible($studentId);
        $eventIdsWithBarcode = array_keys($byEvent);
        $eligibleEventsWithoutBarcode = [];
        foreach ($eligibleEventIds as $eid) {
            if (in_array($eid, $eventIdsWithBarcode, true)) {
                continue;
            }
            $ev = $eventModel->find($eid);
            if (!$ev) {
                continue;
            }
            $unassigned = $barcodeModel->getByEvent($eid, true);
            if (empty($unassigned)) {
                continue;
            }
            $eligibleEventsWithoutBarcode[] = [
                'event_id' => $eid,
                'event_title' => $ev['title'] ?? 'Event #' . $eid,
                'event_date' => $ev['event_date'] ?? null,
            ];
        }

        $data = [
            'page_title' => 'บาร์โค้ดของฉัน',
            'by_event' => $byEvent,
            'eligible_events_without_barcode' => $eligibleEventsWithoutBarcode,
        ];
        return view('student/dashboard/barcodes', $data);
    }

    /**
     * รับบาร์โค้ดหนึ่งรหัสจากกองของ Event (ผูกกับผู้ใช้และเปิดดูได้ทันที)
     */
    public function claimFromEvent($eventId)
    {
        $studentId = (int) session()->get('student_id');
        if (!$studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $eligibleModel = new BarcodeEventEligibleModel();
        $barcodeModel = new BarcodeModel();

        if (!$eligibleModel->isEligible((int) $eventId, $studentId)) {
            return redirect()->to(base_url('student/barcodes'))->with('error', 'คุณไม่มีสิทธิ์รับบาร์โค้ดจาก Event นี้');
        }

        $myBarcodesInEvent = $barcodeModel->where('barcode_event_id', (int) $eventId)->where('student_user_id', $studentId)->findAll();
        if (!empty($myBarcodesInEvent)) {
            return redirect()->to(base_url('student/barcodes'))->with('error', 'คุณรับบาร์โค้ดจาก Event นี้แล้ว');
        }

        $unassigned = $barcodeModel->getByEvent((int) $eventId, true);
        if (empty($unassigned)) {
            return redirect()->to(base_url('student/barcodes'))->with('error', 'ไม่มีบาร์โค้ดว่างใน Event นี้');
        }

        $barcodeId = (int) $unassigned[0]['id'];
        $barcodeModel->assignToStudent($barcodeId, $studentId);
        $barcodeModel->claimByStudent($barcodeId, $studentId);

        return redirect()->to(base_url('student/barcodes'))->with('success', 'รับบาร์โค้ดแล้ว');
    }

    /**
     * Student claims (confirms receipt of) a barcode — sets claimed_at so code is shown.
     */
    public function claimBarcode($barcodeId)
    {
        $studentId = (int) session()->get('student_id');
        if (!$studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }
        $barcodeModel = new BarcodeModel();
        if ($barcodeModel->claimByStudent((int) $barcodeId, $studentId)) {
            return redirect()->to(base_url('student/barcodes'))->with('success', 'รับบาร์โค้ดแล้ว');
        }
        return redirect()->to(base_url('student/barcodes'))->with('error', 'รับบาร์โค้ดไม่สำเร็จ');
    }

    /**
     * ข่าว/Event — กิจกรรมที่กำลังจะมาถึง (จากตาราง events)
     */
    public function events()
    {
        if (!(int) session()->get('student_id')) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $eventModel = new EventModel();
        $events = $eventModel->getUpcoming(20, 0);

        $data = [
            'page_title' => 'ข่าว / Event',
            'events' => $events,
        ];
        return view('student/dashboard/events', $data);
    }
}
