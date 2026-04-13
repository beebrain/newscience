<?php

namespace App\Controllers\User;

use App\Controllers\Admin\CertEvents as AdminCertEvents;
use App\Libraries\CertOrganizerAccess;

/**
 * กิจกรรม E-Certificate บนเว็บ (Dashboard) — ผู้ใช้ทั่วไปจัดการกิจกรรมของตน;
 * ผู้ดูแลระดับคณะ (faculty_admin / super_admin) เห็นรายการทั้งคณะและรายงานรวม
 */
class CertEvents extends AdminCertEvents
{
    public function __construct()
    {
        parent::__construct();
        $this->routePrefix              = 'dashboard/cert-events';
        $this->viewPrefix               = 'user/cert_events';
        $this->scopeIndexToCreatorOnly  = CertOrganizerAccess::isDashboardOrganizerOnly();
    }

    /**
     * รายงานใบที่ออกแล้วทั้งระบบ — เฉพาะผู้ดูแลระดับคณะ
     */
    public function issuedReport()
    {
        if (! CertOrganizerAccess::isFacultyWideViewer()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'ไม่พบหน้าที่ร้องขอ');
        }

        return parent::issuedReport();
    }
}
