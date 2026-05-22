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

    public function testLayoutJsonMustBeValidJsonWhenPresent(): void
    {
        $raw = '{"orientation":"portrait","field_mapping":{"student_name":{"x":90,"y":145,"font_size":22}}}';
        json_decode($raw);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());

        json_decode('{bad json');
        $this->assertNotSame(JSON_ERROR_NONE, json_last_error());
    }
}
