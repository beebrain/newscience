<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Catalog สำหรับระบบรับสมัครงานสัปดาห์วิทยาศาสตร์ 2569
 * Single source of truth — controller/view/validation อ่านจากที่นี่
 */
class SciWeek extends BaseConfig
{
    /**
     * URL prefix ของโมดูลนี้
     */
    public string $routePrefix = 'scienceweek';

    /**
     * รายการแข่งขันทั้ง 5 รายการ
     * ชุดข้อมูลแต่ละรายการ:
     *   name_th       ชื่อภาษาไทย
     *   name_en       ชื่อภาษาอังกฤษ
     *   levels        ระดับที่เปิดรับ ['key' => 'label']
     *   team_min      จำนวนสมาชิกขั้นต่ำ (คนหลัก)
     *   team_max      จำนวนสมาชิกสูงสุด (คนหลัก)
     *   has_reserve   มีตัวสำรองหรือไม่
     *   reserve_max   จำนวนตัวสำรองสูงสุด
     *   per_person    ฟิลด์พิเศษต่อผู้เข้าแข่งขัน: ['field' => ['label','required'(bool),'type']]
     *   cap_per_level จำนวนทีมสูงสุดต่อระดับ (null = ไม่จำกัด)
     *   cap_total     จำนวนทีมสูงสุดรวมทุกระดับ (null = ไม่จำกัด)
     *   cap_per_school จำนวนทีมสูงสุดต่อสถาบัน/ระดับ (null = ไม่จำกัด)
     *   deadline      วันปิดรับ 'YYYY-MM-DD' หรือ null (เปิดตลอด)
     *   contact       ข้อมูลผู้รับผิดชอบ
     *   extra_coaches ครูที่ปรึกษาได้มากกว่า 1 คน (max)
     *   notes         หมายเหตุพิเศษ
     */
    public array $competitions = [

        'seed_art' => [
            'name_th'       => 'ศิลปะจากเมล็ดพันธุ์สำหรับนักสร้างสรรค์รุ่นเยาว์',
            'name_en'       => 'Seed Art Contest for Young Creators',
            'docs'          => ['69-05-13 ใบสมัครศิลปะจากเมล็ดพันธุ์ (2).pdf'],
            'levels'        => [
                'primary'         => 'ระดับประถมศึกษา',
                'lower_secondary' => 'ระดับมัธยมศึกษาตอนต้น',
            ],
            'team_min'      => 3,
            'team_max'      => 3,
            'has_reserve'   => false,
            'reserve_max'   => 0,
            'per_person'    => [
                'level_class' => ['label' => 'กำลังศึกษาอยู่ชั้น', 'required' => true, 'type' => 'text'],
            ],
            'cap_per_level' => 15,
            'cap_total'     => null,
            'cap_per_school'=> null,
            'deadline'      => '2026-07-31', // ตรวจสอบกับผู้จัด ก่อน go-live
            'extra_coaches' => 1,
            'contact'       => 'ผศ.ดร.สุทธิดา วิทยาลัย 089-858-6805',
            'notes'         => 'ทีมละ 3 คน (คงที่) จำกัด 15 ทีม/ระดับ',
        ],

        'rov' => [
            'name_th'       => 'การแข่งขันกีฬา E-sport ROV Battle Tournament 2026',
            'name_en'       => 'E-sport ROV Battle Tournament 2026 by CS-IT URU',
            'docs'          => ['กติกาการรับสมัคร ROV 2026.pdf'],
            'levels'        => [
                'primary_lower'  => 'ระดับประถมศึกษา – มัธยมศึกษาตอนต้น',
                'lower_higher'   => 'ระดับมัธยมศึกษาตอนต้น – อุดมศึกษา',
            ],
            'team_min'      => 5,
            'team_max'      => 5,
            'has_reserve'   => true,
            'reserve_max'   => 2,
            'per_person'    => [
                'level_class' => ['label' => 'ระดับชั้น', 'required' => false, 'type' => 'text'],
                'game_id'     => ['label' => 'ไอดีในเกม (ROV ID)', 'required' => true, 'type' => 'text'],
            ],
            'cap_per_level' => null,
            'cap_total'     => 16,  // 16 ทีมรวมทุกระดับ
            'cap_per_school'=> 1,   // 1 ทีม/สถาบัน/ระดับ
            'deadline'      => '2026-08-17',
            'extra_coaches' => 1,
            'contact'       => 'อ.อนุชา เรืองศิริวัฒนกุล anucha@uru.ac.th 089-707-2231',
            'notes'         => 'สมาชิกหลัก 5 คน + สำรอง 0–2 คน; 1 ทีม/สถาบัน/ระดับ; รวมทุกระดับไม่เกิน 16 ทีม',
        ],

        'python' => [
            'name_th'       => 'การแข่งขันทักษะการเขียนโปรแกรมภาษาไพธอน',
            'name_en'       => 'Python Programming Competition',
            'docs'          => ['กติกาการแข่งขันทักษะการเขียนโปรแกรมภาษา.pdf'],
            'levels'        => [
                'secondary' => 'ระดับมัธยมศึกษาหรือเทียบเท่า',
                'higher'    => 'ระดับอุดมศึกษาหรือเทียบเท่า',
            ],
            'team_min'      => 2,
            'team_max'      => 2,
            'has_reserve'   => false,
            'reserve_max'   => 0,
            'per_person'    => [
                'level_class' => ['label' => 'ระดับชั้น', 'required' => true, 'type' => 'text'],
            ],
            'cap_per_level' => null,
            'cap_total'     => null,
            'cap_per_school'=> 2,   // ≤2 ทีม/สถาบัน (ทุกระดับรวมกัน per spec)
            'deadline'      => null, // TODO ยืนยันกับ อ.พรเทพ
            'extra_coaches' => 1,
            'contact'       => 'อ.พรเทพ จันทร์เพ็ง pornthep@uru.ac.th 089-957-0965',
            'notes'         => 'ทีมละ 2 คน ≤2 ทีม/สถาบัน',
        ],

        'recycle' => [
            'name_th'       => 'การประกวดออกแบบชุดรีไซเคิล',
            'name_en'       => 'Recycle Fashion Design Contest',
            'docs'          => ['ใบสมัครการแข่งขัน.pdf'],
            'levels'        => [
                'primary'   => 'ระดับประถมศึกษา',
                'secondary' => 'ระดับมัธยมศึกษา',
            ],
            'team_min'      => 1,
            'team_max'      => 5,
            'has_reserve'   => false,
            'reserve_max'   => 0,
            'per_person'    => [
                'level_class' => ['label' => 'ระดับชั้น', 'required' => false, 'type' => 'text'],
            ],
            'cap_per_level' => null,
            'cap_total'     => null,
            'cap_per_school'=> null,
            'deadline'      => '2026-08-07',
            'extra_coaches' => 2,   // ครูที่ปรึกษาได้ 2 คน
            'contact'       => 'รศ.ดร.ศรัณยู เรือนจันทร์ 081-786-1566',
            'notes'         => 'ทีม 1–5 คน, ต้องใช้วัสดุรีไซเคิลเท่านั้น',
        ],

        'sci_drawing' => [
            'name_th'       => 'การประกวดภาพวาดจินตนาการทางวิทยาศาสตร์',
            'name_en'       => 'Scientific Imagination Drawing Contest',
            'docs'          => ['ใบสมัครกิจกรรมวาดภาพ ปี 69.pdf'],
            'levels'        => [
                'primary_upper'   => 'ระดับประถมศึกษาตอนปลาย',
                'lower_secondary' => 'ระดับมัธยมศึกษาตอนต้น',
            ],
            'team_min'      => 1,
            'team_max'      => 1, // เดี่ยว
            'has_reserve'   => false,
            'reserve_max'   => 0,
            'per_person'    => [
                'level_class' => ['label' => 'ชั้นมัธยมศึกษาปีที่ / ชั้นที่', 'required' => false, 'type' => 'text'],
                'age'         => ['label' => 'อายุ (ปี)', 'required' => true, 'type' => 'number'],
                'occupation'  => ['label' => 'อาชีพ', 'required' => false, 'type' => 'text'],
                'line_id'     => ['label' => 'ID Line', 'required' => false, 'type' => 'text'],
            ],
            'cap_per_level' => null,
            'cap_total'     => null,
            'cap_per_school'=> null,
            'deadline'      => '2026-07-31',
            'extra_coaches' => 2,   // ครูที่ปรึกษาได้ 2 คน
            'contact'       => 'ผศ.ดร.สุภาพร พงศ์ธรพฤกษ์ ajann_envi@uru.ac.th 089-704-4407',
            'notes'         => 'ประกวดเดี่ยว, ใช้ดินสอขาวดำ, หัวข้อ "โลกสีเขียวแห่งอนาคต"',
        ],
    ];
}
