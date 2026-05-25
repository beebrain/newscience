<?php
/**
 * สถานะกิจกรรมบาร์โค้ด — ใช้ร่วมกันระหว่างรายการและหน้ารายละเอียด
 *
 * @return array<string, array{label: string, hint: string, tone: string}>
 */
function student_activity_state_meta(): array
{
    return [
        'locked'          => [
            'label' => 'ไม่มีสิทธิ์',
            'hint'  => 'ติดต่อผู้จัดหรือกรอกรหัสเข้าร่วม',
            'tone'  => 'muted',
        ],
        'ready_claim'     => [
            'label' => 'พร้อมรับรหัส',
            'hint'  => 'กดเข้าไปรับรหัสของคุณ',
            'tone'  => 'action',
        ],
        'confirm_receipt' => [
            'label' => 'รอยืนยัน',
            'hint'  => 'ยืนยันเพื่อดูรหัส',
            'tone'  => 'action',
        ],
        'wait_pool'       => [
            'label' => 'รอรหัสว่าง',
            'hint'  => 'กลับมาตรวจอีกครั้งภายหลัง',
            'tone'  => 'warn',
        ],
        'opened'          => [
            'label' => 'รับรหัสแล้ว',
            'hint'  => 'ดูรหัสของคุณได้แล้ว',
            'tone'  => 'success',
        ],
        'event_closed'    => [
            'label' => 'ปิดรับแล้ว',
            'hint'  => 'ไม่สามารถรับรหัสเพิ่มได้',
            'tone'  => 'muted',
        ],
    ];
}

function student_activity_format_date(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }
    $ts = strtotime($date);

    return $ts ? date('d/m/Y', $ts) : $date;
}
