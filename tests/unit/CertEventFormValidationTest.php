<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * ฟอร์มกิจกรรมใบรับรอง: กติกา validation หลักหลังตัดฟิลด์สถานะ/ผู้ลงนามออกจากฟอร์ม
 *
 * @internal
 */
final class CertEventFormValidationTest extends CIUnitTestCase
{
    public function testTitleRequired(): void
    {
        $v = \Config\Services::validation(null, false);
        $v->setRules([
            'title'      => 'required|min_length[3]',
            'event_date' => 'permit_empty|valid_date',
        ]);

        $this->assertFalse($v->run(['title' => 'ab', 'event_date' => '']));
        $v = \Config\Services::validation(null, false);
        $v->setRules([
            'title'      => 'required|min_length[3]',
            'event_date' => 'permit_empty|valid_date',
        ]);
        $this->assertTrue($v->run([
            'title'      => 'อบรมทดสอบระบบ',
            'event_date' => '',
        ]));
    }
}
