<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * โครง default ของ section บนหน้า /p/{id}/onepage
 * ต่อยอด: เพิ่มแถวใน $sectionDefinitions แล้ว deploy — ค่าใน onepage_json จะ merge อัตโนมัติ
 */
class ProgramOnepage extends BaseConfig
{
    /**
     * @var list<array{id: string, title: string, aun_hint: string, description: string}>
     */
    public array $sectionDefinitions = [
        [
            'id'          => 'philosophy',
            'title'       => 'ปรัชญาและทิศทางการจัดการเรียนรู้',
            'aun_hint'    => '3.1',
            'description' => 'ปรัชญา อุดมการณ์ แนวคิดจัดการเรียนรู้',
        ],
        [
            'id'          => 'programme_spec',
            'title'       => 'ชื่อหลักสูตร วุฒิ สาขา (รายละเอียดเพิ่มเติม)',
            'aun_hint'    => '2.1',
            'description' => 'เสริมข้อมูลจากระบบ — อธิบายโปรแกรม/สาขาให้ชัด',
        ],
        [
            'id'          => 'plo',
            'title'       => 'ผลลัพธ์การเรียนรู้ (PLO / ELO)',
            'aun_hint'    => '1.1, 1.3, 1.5',
            'description' => 'OBE: รายข้อ วัดได้',
        ],
        [
            'id'          => 'stakeholder',
            'title'       => 'ความเชื่อมโยง PLO กับผู้มีส่วนได้ส่วนเสีย',
            'aun_hint'    => '1.4',
            'description' => 'อุตสาหกรรม บัณฑิต นักศึกษา ฯลฯ',
        ],
        [
            'id'          => 'structure',
            'title'       => 'โครงสร้างหลักสูตร',
            'aun_hint'    => '2.1, 2.5',
            'description' => 'GE / เอก / เลือก / หน่วยกิต เป็นตารางหรือคำอธิบาย',
        ],
        [
            'id'          => 'study_plan',
            'title'       => 'แผนการศึกษา / รายวิชาต่อภาค',
            'aun_hint'    => '2.5',
            'description' => 'Progression ปี/ภาค',
        ],
        [
            'id'          => 'course_spec',
            'title'       => 'รายละเอียดรายวิชา (syllabus, CLO, ฯลฯ)',
            'aun_hint'    => '2.1, 2.4',
            'description' => 'อ้างอิงเอกสารดาวน์โหลดได้',
        ],
        [
            'id'          => 'curriculum_mapping',
            'title'       => 'Curriculum mapping (วิชา – PLO)',
            'aun_hint'    => '2.4',
            'description' => 'ตารางเชื่อมโยง',
        ],
        [
            'id'          => 'pedagogy',
            'title'       => 'วิธีการจัดการเรียนรู้',
            'aun_hint'    => '3.2–3.5',
            'description' => 'AL, โปรเจกต์, สหกิจ, ฝึกงาน ฯลฯ',
        ],
        [
            'id'          => 'assessment',
            'title'       => 'วิธีการประเมินผลนักศึกษา',
            'aun_hint'    => '4.1, 4.4, 4.5',
            'description' => 'สัดส่วนคะแนน สอบ โปรเจกต์ Rubric',
        ],
        [
            'id'          => 'appeal',
            'title'       => 'นโยบายอุทธรณ์ / ร้องเรียน',
            'aun_hint'    => '4.2',
            'description' => 'ขั้นตอน ช่องทาง',
        ],
        [
            'id'          => 'graduation',
            'title'       => 'เกณฑ์สำเร็จการศึกษา',
            'aun_hint'    => '4.3',
            'description' => 'GPA, ภาษา, สหกิจ, วิจัย ฯลฯ',
        ],
        [
            'id'          => 'faculty',
            'title'       => 'อาจารย์และคุณวุฒิ',
            'aun_hint'    => '5.1–5.8',
            'description' => 'เนื้อหาเสริม (รายชื่ออาจแสดงจากเว็บหลัก)',
        ],
        [
            'id'          => 'student_support',
            'title'       => 'การสนับสนุนนักศึกษา',
            'aun_hint'    => '6.x',
            'description' => 'ทุน ที่ปรึกษา ระบบดูแล',
        ],
        [
            'id'          => 'facilities',
            'title'       => 'สิ่งอำนวยความสะดวก / สถานที่',
            'aun_hint'    => '7.x',
            'description' => 'ห้อง Lab ห้องสมุด อุปกรณ์ (แนะนำใส่รูป)',
        ],
        [
            'id'          => 'graduate_outcomes',
            'title'       => 'ผลลัพธ์บัณฑิต (อาชีพ ศึกษาต่อ รางวัล)',
            'aun_hint'    => '8.1–8.4',
            'description' => 'ข้อมูลเชิงรับรู้ของผู้สมัคร',
        ],
        [
            'id'          => 'contact',
            'title'       => 'ช่องทางติดต่อ',
            'aun_hint'    => '—',
            'description' => 'โทร อีเมล Social ที่ตั้ง',
        ],
    ];

    /**
     * รวมค่า default กับ onepage_json ที่เก็บใน DB
     *
     * @return list<array{id: string, title: string, aun_hint: string, description: string, body: string, hidden: bool}>
     */
    public function buildSectionsForView(?string $onepageJson): array
    {
        $stored = [];
        if ($onepageJson !== null && $onepageJson !== '') {
            $decoded = json_decode($onepageJson, true);
            if (is_array($decoded) && isset($decoded['sections']) && is_array($decoded['sections'])) {
                $stored = $decoded['sections'];
            }
        }

        $out = [];
        foreach ($this->sectionDefinitions as $def) {
            $id  = $def['id'];
            $row = is_array($stored[$id] ?? null) ? $stored[$id] : [];
            $body = (string) ($row['body'] ?? '');
            $titleOverride = trim((string) ($row['title_override'] ?? ''));
            $hidden = ! empty($row['hidden']);

            $out[] = [
                'id'              => $id,
                'title'           => $titleOverride !== '' ? $titleOverride : $def['title'],
                'default_title'   => $def['title'] ?? '',
                'title_override'  => $titleOverride,
                'aun_hint'        => $def['aun_hint'] ?? '',
                'description'     => $def['description'] ?? '',
                'body'            => $body,
                'hidden'          => $hidden,
            ];
        }

        return $out;
    }

    /**
     * Section ที่จะแสดงบนหน้า (ไม่ hidden และมี body หรือ force แสดงหัวข้อ)
     *
     * @param list<array{id: string, title: string, aun_hint: string, description: string, body: string, hidden: bool}> $sections
     *
     * @return list<array<string, mixed>>
     */
    public function filterVisibleForPublic(array $sections, bool $showEmpty = false): array
    {
        $r = [];
        foreach ($sections as $s) {
            if (! empty($s['hidden'])) {
                continue;
            }
            $body  = (string) ($s['body'] ?? '');
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace("\xc2\xa0", ' ', $body))));
            if (! $showEmpty && $plain === '') {
                continue;
            }
            $r[] = $s;
        }

        return $r;
    }
}
