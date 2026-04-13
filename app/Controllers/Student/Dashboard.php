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
     * สถานะกิจกรรมบาร์โค้ดต่อนักศึกษา 1 คน (สำหรับรายการ + หน้าคูปอง)
     *
     * @return array{state: string, my_barcodes: list<array>, first_unclaimed_id: int|null}
     */
    private function computeStudentBarcodeEventPortalState(int $studentId, array $event, BarcodeModel $barcodeModel, BarcodeEventEligibleModel $eligibleModel): array
    {
        $eid    = (int) ($event['id'] ?? 0);
        $status = (string) ($event['status'] ?? 'draft');

        $isEligible = $eligibleModel->isEligible($eid, $studentId);
        $myBarcodes = $barcodeModel->where('barcode_event_id', $eid)->where('student_user_id', $studentId)->findAll();

        if (! $isEligible) {
            return ['state' => 'locked', 'my_barcodes' => [], 'first_unclaimed_id' => null];
        }

        if ($myBarcodes !== []) {
            foreach ($myBarcodes as $b) {
                if (empty($b['claimed_at'])) {
                    return [
                        'state'               => 'confirm_receipt',
                        'my_barcodes'         => $myBarcodes,
                        'first_unclaimed_id'  => (int) ($b['id'] ?? 0) ?: null,
                    ];
                }
            }

            return ['state' => 'opened', 'my_barcodes' => $myBarcodes, 'first_unclaimed_id' => null];
        }

        if ($status !== 'active') {
            return ['state' => 'event_closed', 'my_barcodes' => [], 'first_unclaimed_id' => null];
        }

        $unassigned = $barcodeModel->getByEvent($eid, true);
        if ($unassigned === []) {
            return ['state' => 'wait_pool', 'my_barcodes' => [], 'first_unclaimed_id' => null];
        }

        return ['state' => 'ready_claim', 'my_barcodes' => [], 'first_unclaimed_id' => null];
    }

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
     * บาร์โค้ดของฉัน — แสดงกิจกรรมทั้งหมดที่เปิดเผยได้ + สถานะสิทธิ์ของตนเอง (เข้าแต่ละกิจกรรมเพื่อเปิดคูปองรับสิทธิ์)
     */
    public function barcodes()
    {
        $studentId = (int) session()->get('student_id');
        if (! $studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $barcodeModel  = new BarcodeModel();
        $eventModel    = new BarcodeEventModel();
        $eligibleModel = new BarcodeEventEligibleModel();

        $portalEvents = [];
        foreach ($eventModel->getVisibleForStudentPortal() as $ev) {
            $portalEvents[] = [
                'event' => $ev,
                'state' => $this->computeStudentBarcodeEventPortalState($studentId, $ev, $barcodeModel, $eligibleModel),
            ];
        }

        $data = [
            'page_title'    => 'บาร์โค้ดของฉัน',
            'portal_events' => $portalEvents,
        ];

        return view('student/dashboard/barcodes', $data);
    }

    /**
     * หน้ากิจกรรมเดียว — เปิดคูปองยืนยันการจับคู่บาร์โค้ด
     */
    public function barcodeEvent($eventId)
    {
        $studentId = (int) session()->get('student_id');
        if (! $studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $eventModel = new BarcodeEventModel();
        $event      = $eventModel->find((int) $eventId);
        if (! $event || ($event['status'] ?? '') === 'draft') {
            return redirect()->to(base_url('student/barcodes'))->with('error', 'ไม่พบกิจกรรมนี้');
        }

        $barcodeModel  = new BarcodeModel();
        $eligibleModel = new BarcodeEventEligibleModel();
        $state         = $this->computeStudentBarcodeEventPortalState($studentId, $event, $barcodeModel, $eligibleModel);

        $data = [
            'page_title' => 'กิจกรรม: ' . ($event['title'] ?? ''),
            'event'      => $event,
            'state'      => $state,
        ];

        return view('student/dashboard/barcode_event', $data);
    }

    /**
     * รับบาร์โค้ดหนึ่งรหัสจากกองของ Event (ผูกกับผู้ใช้และเปิดดูได้ทันที)
     */
    public function claimFromEvent($eventId)
    {
        $studentId = (int) session()->get('student_id');
        if (! $studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $eventModel = new BarcodeEventModel();
        $event      = $eventModel->find((int) $eventId);
        if (! $event) {
            return redirect()->to(base_url('student/barcodes'))->with('error', 'ไม่พบกิจกรรม');
        }
        if (($event['status'] ?? '') !== 'active') {
            return redirect()->to(base_url('student/barcodes/event/' . (int) $eventId))->with('error', 'กิจกรรมนี้รับสิทธิ์ไม่ได้ในขณะนี้');
        }

        $eligibleModel = new BarcodeEventEligibleModel();
        $barcodeModel  = new BarcodeModel();

        if (! $eligibleModel->isEligible((int) $eventId, $studentId)) {
            return redirect()->to(base_url('student/barcodes/event/' . (int) $eventId))->with('error', 'คุณไม่มีสิทธิ์รับบาร์โค้ดจากกิจกรรมนี้');
        }

        $myBarcodesInEvent = $barcodeModel->where('barcode_event_id', (int) $eventId)->where('student_user_id', $studentId)->findAll();
        if ($myBarcodesInEvent !== []) {
            return redirect()->to(base_url('student/barcodes/event/' . (int) $eventId))->with('error', 'คุณรับบาร์โค้ดจากกิจกรรมนี้แล้ว');
        }

        $assignedId = $barcodeModel->assignAndClaimFirstAvailableAtomic((int) $eventId, $studentId);
        if ($assignedId === null) {
            return redirect()->to(base_url('student/barcodes/event/' . (int) $eventId))->with('error', 'ไม่มีบาร์โค้ดว่างในกิจกรรมนี้ หรือมีผู้รับพร้อมกัน กรุณาลองใหม่');
        }

        return redirect()->to(base_url('student/barcodes/event/' . (int) $eventId))->with('success', 'เปิดคูปองรับสิทธิ์สำเร็จ — บันทึกการจับคู่ของคุณแล้ว');
    }

    /**
     * Student claims (confirms receipt of) a barcode — sets claimed_at so code is shown.
     */
    public function claimBarcode($barcodeId)
    {
        $studentId = (int) session()->get('student_id');
        if (! $studentId) {
            return redirect()->to(base_url('student/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }
        $barcodeModel = new BarcodeModel();
        $row          = $barcodeModel->find((int) $barcodeId);
        if (! $row) {
            return redirect()->to(base_url('student/barcodes'))->with('error', 'ไม่พบบาร์โค้ด');
        }
        $eventId = (int) ($row['barcode_event_id'] ?? 0);
        if ($barcodeModel->claimByStudent((int) $barcodeId, $studentId)) {
            if ($eventId > 0) {
                return redirect()->to(base_url('student/barcodes/event/' . $eventId))->with('success', 'ยืนยันการรับสิทธิ์แล้ว — นี่คือรหัสของคุณ');
            }

            return redirect()->to(base_url('student/barcodes'))->with('success', 'รับบาร์โค้ดแล้ว');
        }

        if ($eventId > 0) {
            return redirect()->to(base_url('student/barcodes/event/' . $eventId))->with('error', 'ยืนยันการรับไม่สำเร็จ');
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
