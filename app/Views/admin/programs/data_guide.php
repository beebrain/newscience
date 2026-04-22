<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
$pid = (int) ($program['id'] ?? 0);
$u   = static function (string $path): string {
    return base_url($path);
};
$edit = static function (string $tab, ?string $sub = null) use ($pid): string {
    $q = ['tab' => $tab];
    if ($sub !== null && $sub !== '') {
        $q['sub'] = $sub;
    }

    return base_url('program-admin/edit/' . $pid) . '?' . http_build_query($q);
};

$chapters = [
    [
        'id'    => 'overview',
        'title' => 'บทนำ & วิธีใช้หน้านี้',
        'rows'  => null,
    ],
    [
        'id'    => 'philosophy',
        'title' => 'ปรัชญา ชื่อหลักสูตร วุฒิ (เกณฑ์ 2.x, 3.1)',
        'rows'  => [
            [
                'topic'  => 'ปรัชญาการศึกษา / ทิศทางการจัดการเรียนรู้ (Educational philosophy)',
                'aun'    => '3.1',
                'detail' => 'เผยแพร่ต่อผู้มีส่วนได้ส่วนเสีย — ปรัชญา อุดมการณ์ แนวทางสอน',
                'where'  => 'แก้เนื้อหา แท็บย่อย 1. ภาพรวม (ปรัชญา วัตถุประสงค์ คุณลักษณะบัณฑิต)',
                'href'   => $edit('content', 'overview'),
            ],
            [
                'topic'  => 'ชื่อหลักสูตร วุฒิ สาขา (ไทย–อังกฤษ)',
                'aun'    => '2.1',
                'detail' => 'Programme specification ระดับพื้นฐาน',
                'where'  => 'แก้ ข้อมูลพื้นฐาน (ชื่อ ระดับ) + รูปหน้าปก',
                'href'   => $edit('basic'),
            ],
        ],
    ],
    [
        'id'    => 'obe',
        'title' => 'OBE: PLO / ELO, มาตรฐาน, สเตกโฮลเดอร์ (1.x)',
        'rows'  => [
            [
                'topic'  => 'PLOs / ELOs — ชัดเจน วัดได้',
                'aun'    => '1.1, 1.3, 1.5',
                'detail' => 'แก่นของ OBE',
                'where'  => 'แก้เนื้อหา แท็บย่อย 2. มาตรฐาน & PLO (บล็อก PLO/ELO)',
                'href'   => $edit('content', 'quality'),
            ],
            [
                'topic'  => 'มาตรฐานการเรียนรู้ + ตารางเชื่อมมาตรฐาน–PLO',
                'aun'    => '1.1',
                'detail' => 'รายงาน/หน้าเว็บ AUN',
                'where'  => 'แก้ แท็บย่อย 2 (มาตรฐาน + ตาราง mapping)',
                'href'   => $edit('content', 'quality'),
            ],
            [
                'topic'  => 'ความเชื่อมโยง PLO กับผู้มีส่วนได้ส่วนเสีย (อุตสาหกรรม นศ. ฯลฯ)',
                'aun'    => '1.4',
                'detail' => 'อธิบายที่มา PLO — อาจใส่คำอธิบายในคำนำมาตรฐาน หรือแนบรายงาน',
                'where'  => 'แท็บ 2 คำอธิบายมาตรฐาน หรือ ดาวน์โหลด (PDF) + ลิงก์ในข้อความ',
                'href'   => $edit('content', 'quality'),
            ],
        ],
    ],
    [
        'id'    => 'curriculum',
        'title' => 'โครงสร้าง & แผนการศึกษา & รายวิชา (2.x)',
        'rows'  => [
            [
                'topic'  => 'โครงสร้างหลักสูตร (GE / เอก / เลือก / หน่วยกิต)',
                'aun'    => '2.1, 2.5',
                'detail' => 'ควรเป็นตารางอ่านง่าย หรือกราฟิก + อธิบาย',
                'where'  => 'แก้ แท็บย่อย 3 ช่อง «โครงสร้างหลักสูตร (HTML)» + รูป/PDF แทรกได้',
                'href'   => $edit('content', 'curriculum'),
            ],
            [
                'topic'  => 'แผนการศึกษา รายวิชาต่อภาคเรียน ปี 1–4+',
                'aun'    => '2.5',
                'detail' => 'สะท้อน progression ของหลักสูตร',
                'where'  => 'แก้ แท็บย่อย 3: ตารางรายวิชา (ข้อมูลโครงสร้าง) + แผนการเรียน (HTML)',
                'href'   => $edit('content', 'curriculum'),
            ],
            [
                'topic'  => 'Curriculum mapping รายวิชา–PLO',
                'aun'    => '2.4',
                'detail' => 'หลักฐานสำคัญ — ตารางหรือไฟล์',
                'where'  => 'ใส่ตารางในข้อความ หรืออัปโหลด PDF แท็บ ดาวน์โหลด แล้วลิงก์',
                'href'   => $edit('downloads'),
            ],
            [
                'topic'  => 'รายละเอียดรายวิชา (syllabus, CLO, วิธีสอน–ประเมิน)',
                'aun'    => '2.1, 2.4',
                'detail' => 'มุ่งให้ดาวน์โหลด syllabus ต่อรายวิชา',
                'where'  => 'แท็บ ดาวน์โหลด (Word/PDF) + อ้างอิงใน แท็บ 3/4 ตามความเหมาะสม',
                'href'   => $edit('downloads'),
            ],
        ],
    ],
    [
        'id'    => 'pedagogy',
        'title' => 'วิธีสอน & วิธีประเมิน (3.x, 4.x)',
        'rows'  => [
            [
                'topic'  => 'วิธีจัดการเรียนรู้ (AL, โปรเจกต์, สหกิจ, ฝึกงาน ฯลฯ)',
                'aun'    => '3.2–3.5',
                'detail' => 'เน้นจุดเด่นมหาวิทยาลัย/หลักสูตร',
                'where'  => 'แท็บย่อย 3–4 หรือแท็บ ดาวน์โหลด (คู่มือยาว) + รูปแทรก',
                'href'   => $edit('content', 'pages'),
            ],
            [
                'topic'  => 'วิธีประเมิน สัดส่วนคะแนน Rubric สอบ/Project',
                'aun'    => '4.1, 4.4, 4.5',
                'detail' => 'ต้องชัด โปร่งใส',
                'where'  => 'ข้อความ HTML แท็บ 3/4 หรือ ดาวน์โหลด นโยบายการให้คะแนน',
                'href'   => $edit('content', 'curriculum'),
            ],
            [
                'topic'  => 'นโยบายอุทธรณ์ / ร้องเรียน',
                'aun'    => '4.2',
                'detail' => 'มักขาด — แนะนำแยก PDF + ลิงก์ชัด',
                'where'  => 'แนบ ดาวน์โหลด + ลิงก์ย่อในช่อง «การรับสมัคร/ติดต่อ» ตามดุลยาพินิจ',
                'href'   => $edit('downloads'),
            ],
            [
                'topic'  => 'เกณฑ์สำเร็จการศึกษา (GPA, ภาษา, สหกิจ, วิจัย ฯลฯ)',
                'aun'    => '4.3',
                'detail' => 'สำคัญมาก',
                'where'  => 'ช่อง «การรับสมัคร/ค่าใช้จ่าย/แผน» ตามรูปแบบหลักสูตร หรือ PDF',
                'href'   => $edit('content', 'pages'),
            ],
        ],
    ],
    [
        'id'    => 'people',
        'title' => 'อาจารย์ การสนับสนุนนักศึกษา (5.x, 6.x)',
        'rows'  => [
            [
                'topic'  => 'อาจารย์ประจำหลักสูตร คุณวุฒิ ความเชี่ยวชาญ',
                'aun'    => '5.1–5.8',
                'detail' => 'เชื่อมกับฐานบุคลากร',
                'where'  => 'แก้ แท็บ บุคลากร (อ่านอย่างเดียว) — รายละเอียดเพิ่มผ่านผู้ดูแลระบบ / รูปปกคณาจารย์ในหน้าเว็บมาจากข้อมูลระบบ',
                'href'   => $edit('personnel'),
            ],
            [
                'topic'  => 'การสนับสนุนนักศึกษา ทุน ที่ปรึกษา ระบบดูแล',
                'aun'    => '6.x',
                'detail' => 'บริการนักศึกษา',
                'where'  => 'ช่อง «การรับสมัคร/ติดต่อ» หรือ ดาวน์โหลด (คู่มือ) + ข่าว',
                'href'   => $edit('content', 'pages'),
            ],
        ],
    ],
    [
        'id'    => 'facilities',
        'title' => 'สิ่งอำนวยความสะดวก (7.x)',
        'rows'  => [
            [
                'topic'  => 'ห้อง Lab ห้องสมุด อุปกรณ์ โครงข่าย ฯลฯ',
                'aun'    => '7.x',
                'detail' => 'แนะนำมีรูปประกอบ',
                'where'  => 'อัปโหลดรูป แทรกใน แท็บ 3/4 หรือ แกลเลอรี/ข้อความ ตามที่ระบบรองรับ + วิดีโอแนะนำ (ลิงก์)',
                'href'   => $edit('content', 'pages'),
            ],
        ],
    ],
    [
        'id'    => 'outcomes',
        'title' => 'ผลลัพธ์บัณฑิต ข่าว กิจกรรม (8.x)',
        'rows'  => [
            [
                'topic'  => 'การจ้างงาน ศึกษาต่อ รางวัล ผลงานเด่น',
                'aun'    => '8.1–8.4',
                'detail' => 'ดึงดูดผู้สมัคร',
                'where'  => 'แท็บ ศิษย์เก่า + ช่อง «อาชีพ/แนวโน้ม» + รูป/ข่าว',
                'href'   => $edit('alumni'),
            ],
            [
                'topic'  => 'ข่าว ประชาสัมพันธ์ กิจกรรม',
                'aun'    => '8.x',
                'detail' => 'แสดงภาพลักษณ์ active',
                'where'  => 'แก้ แท็บ ข่าวหลักสูตร หรือ กิจกรรม (หน้าแยกตามลิงก์)',
                'href'   => $edit('news'),
            ],
            [
                'topic'  => 'กิจกรรมหลักสูตร (รายการกิจกรรม)',
                'aun'    => '8.x',
                'detail' => 'จัดรายการกิจกรรมแยก',
                'where'  => 'หน้า «กิจกรรม» สำหรับหลักสูตรนี้',
                'href'   => $u('program-admin/activities/' . $pid),
            ],
        ],
    ],
    [
        'id'    => 'contact',
        'title' => 'ช่องทางติดต่อ & สี/การเผยแพร่',
        'rows'  => [
            [
                'topic'  => 'เบอร์ อีเมล Social Line ที่ตั้ง',
                'aun'    => '—',
                'detail' => 'เข้าถึงง่าย สอดคล้องทุกเกณฑ์ที่ต้องชี้ติดต่อ',
                'where'  => 'แท็บย่อย 4 ข้อมูลติดต่อ (และช่องอื่นในกลุ่มเดียวกันตามเหมาะสม)',
                'href'   => $edit('content', 'pages'),
            ],
            [
                'topic'  => 'เผยแพร่ SEO รูป Hero (สำรอง) ฯลฯ',
                'aun'    => '—',
                'detail' => 'สถานะเผยแพร่ คำอธิบาย Google',
                'where'  => 'แท็บเนื้อหา แท็บย่อย 5. เผยแพร่ (และรูป hero ร่วมกับข้อมูลพื้นฐาน)',
                'href'   => $edit('content', 'publish'),
            ],
            [
                'topic'  => 'สีธีมเว็บ สีตัวอักษร พื้นหลัง',
                'aun'    => '—',
                'detail' => 'รูปลักษณ์หน้าเว็บหลักสูตร',
                'where'  => 'แท็บ การตั้งค่าเว็บไซต์',
                'href'   => $edit('website'),
            ],
        ],
    ],
];
?>
<div class="card program-data-guide">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2 style="margin:0;"><?= esc($page_title) ?></h2>
            <p class="form-text text-muted" style="margin: 0.5rem 0 0; font-size: 0.9rem; max-width: 48rem;">หน้านี้สรุป <strong>ข้อมูลที่ควรเผยแพร่</strong> บนเว็บหลักสูตร โดยอ้างอิงเกณฑ์ AUN-QA แบบย่อ ช่วยตรวจรายการก่อน/หลังกรอก แล้วใช้ลิงก์ «ไปกรอก» เพื่อเปิดแท็บแก้ไขตรงจุด</p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                <a href="<?= $u('program-admin') ?>" class="btn btn-secondary btn-sm">กลับแดชบอร์ด</a>
                <a href="<?= $u('program-admin/edit/' . $pid) ?>" class="btn btn-primary btn-sm">แก้ไขเนื้อหาหลักสูตร</a>
                <a href="<?= $u('program-admin/preview/' . $pid) ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">ดูตัวอย่างเว็บ</a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">

        <nav class="data-guide-toc" aria-label="กระโดดไปยังหัวข้อ" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 8px;">
            <strong style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">กระโดดไปยังหัวข้อ</strong>
            <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 0.35rem 0.75rem; font-size: 0.875rem;">
                <?php foreach ($chapters as $ch) : ?>
                    <?php if ($ch['id'] === 'overview') { continue; } ?>
                    <li><a href="#<?= esc($ch['id'], 'attr') ?>"><?= esc($ch['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <section class="data-guide-block" id="overview" style="margin-bottom: 1.5rem; padding: 1rem; border-left: 4px solid var(--color-primary, #2563eb); background: #f8fafc; border-radius: 0 8px 8px 0;">
            <h3 class="form-section-title" style="margin-top:0;">วิธีใช้</h3>
            <ol style="margin: 0.5rem 0 0; padding-left: 1.25rem; line-height: 1.6; color: var(--color-gray-800);">
                <li>อ่านแต่ละหัวข้อ — คอลัมน์ <strong>เกณฑ์ AUN (โดยย่อ)</strong> อ้างอิง Criterion; <strong>รายละเอียด</strong> อธิบายสิ่งที่ควรมอง visible บนเว็บ</li>
                <li>กด <strong>ไปกรอกข้อมูล</strong> ระบบจะเปิดหน้าแก้ไขแท็บ/แท็บย่อยที่ตรงกับระบบปัจจุบัน</li>
                <li>เนื้อหายาว/ตารางซับซ้อน — แนะนำ <strong>อัปโหลด PDF</strong> ที่แท็บดาวน์โหลด แล้ว <strong>ลิงก์</strong> มาในย่อหน้า</li>
            </ol>
        </section>

        <?php foreach ($chapters as $ch) : ?>
            <?php if ($ch['id'] === 'overview' || $ch['rows'] === null) { continue; } ?>
            <section id="<?= esc($ch['id'], 'attr') ?>" class="data-guide-chapter" style="margin-bottom: 2rem;">
                <h3 class="form-section-title" style="font-size: 1.1rem; border-bottom: 1px solid var(--color-gray-200); padding-bottom: 0.5rem; margin-bottom: 0.75rem;"><?= esc($ch['title']) ?></h3>
                <div style="overflow-x: auto;">
                    <table class="data-guide-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="text-align: left; background: var(--color-gray-100);">
                                <th style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); min-width: 9rem;">หัวข้อ</th>
                                <th style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); width: 4.5rem;">AUN</th>
                                <th style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); min-width: 10rem;">รายละเอียด (ควรมีบนเว็บ)</th>
                                <th style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); min-width: 12rem;">กรอกในระบบที่</th>
                                <th style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); width: 7rem;">ลิงก์</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ch['rows'] as $row) : ?>
                                <tr>
                                    <td style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><?= esc($row['topic']) ?></td>
                                    <td style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); vertical-align: top; color: var(--color-gray-700); font-size: 0.8125rem;"><?= esc($row['aun']) ?></td>
                                    <td style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); vertical-align: top; color: var(--color-gray-700);"><?= esc($row['detail']) ?></td>
                                    <td style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); vertical-align: top; color: var(--color-gray-800);"><?= esc($row['where']) ?></td>
                                    <td style="padding: 0.5rem 0.6rem; border: 1px solid var(--color-gray-200); vertical-align: top; white-space: nowrap;">
                                        <a href="<?= esc($row['href'], 'attr') ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">ไปกรอก</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>

        <p class="form-text text-muted" style="font-size: 0.8125rem; margin-top: 1.5rem;">หมายเหตุ: เกณฑ์ AUN-QA ใช้เป็นแนวทางจัดเนื้อหา; รายละเอียด official ให้ตรวจกับฉบับสถาบันประกันคุณภาพ — รายการนี้ไม่ทับแทน self-assessment ของหลักสูตร</p>
    </div>
</div>

<?= $this->endSection() ?>
