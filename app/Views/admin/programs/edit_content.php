<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <p class="form-text text-muted" style="margin: 0.35rem 0 0; font-size: 0.875rem; max-width: 52rem;">แนะนำ: แก้ไขบนหน้านี้ แล้วใช้ปุ่ม <strong>ดูตัวอย่าง</strong> เพื่อตรวจหน้าเว็บ</p>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('program-admin') ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    กลับ
                </a>
                <a href="<?= base_url('program-admin/preview/' . $program['id']) ?>" class="btn btn-outline btn-sm" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    ดูตัวอย่าง
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <!-- Tab Navigation -->
        <div class="tab-navigation" style="display: flex; border-bottom: 1px solid var(--color-gray-200); background: var(--color-gray-50);">
            <button type="button" class="tab-button active" data-tab="basic" onclick="switchTab('basic')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                ข้อมูลพื้นฐาน
            </button>
            <button type="button" class="tab-button" data-tab="content" onclick="switchTab('content')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                เนื้อหาเว็บหลักสูตร
            </button>
            <button type="button" class="tab-button" data-tab="alumni" onclick="switchTab('alumni')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                </svg>
                ศิษย์เก่าถึงรุ่นน้อง
            </button>
            <button type="button" class="tab-button" data-tab="downloads" onclick="switchTab('downloads')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                ดาวน์โหลด
            </button>
            <button type="button" class="tab-button" data-tab="news" onclick="switchTab('news'); loadProgramNews();">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                ข่าวหลักสูตร
            </button>
            <button type="button" class="tab-button" data-tab="activities" onclick="window.location.href='<?= base_url('program-admin/activities/' . $program['id']) ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 20V10"></path>
                    <path d="M12 20V4"></path>
                    <path d="M6 20v-6"></path>
                </svg>
                กิจกรรม
            </button>
            <button type="button" class="tab-button" data-tab="personnel" onclick="switchTab('personnel')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                </svg>
                บุคลากร
            </button>
            <button type="button" class="tab-button" data-tab="website" onclick="switchTab('website')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-1.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h1.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v1.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-1.09a1.65 1.65 0 00-1.51 1z" />
                </svg>
                การตั้งค่าเว็บไซต์
            </button>
        </div>

        <details class="program-section-matrix-wrap" style="margin: 0; border-bottom: 1px solid var(--color-gray-200); background: #fafafa;">
            <summary class="program-section-matrix-summary" style="cursor: pointer; padding: 0.65rem 1.25rem; font-size: 0.875rem; font-weight: 600; color: var(--color-gray-800); list-style: none;">รายละเอียดแต่ละ Section ที่ควรมี (อ้างอิง)</summary>
            <div class="program-section-matrix-scroll" style="padding: 0 1.25rem 1rem; overflow-x: auto;">
                <p class="form-text text-muted" style="font-size: 0.8125rem; margin: 0 0 0.5rem;">ใช้เป็นตัวเช็กสาระสำคัญที่ควรเผยแพร่และรายละเอียดที่ควรระบุในเนื้อหา ไม่บังคับทุกข้อ</p>
                <table class="program-section-matrix-table" style="width: 100%; min-width: 720px; border-collapse: collapse; font-size: 0.8125rem; line-height: 1.45;">
                    <thead>
                        <tr style="background: var(--color-gray-100);">
                            <th scope="col" style="text-align: left; padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); width: 14%;">หัวข้อหลัก</th>
                            <th scope="col" style="text-align: left; padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); width: 24%;">ข้อมูลที่ควรเผยแพร่</th>
                            <th scope="col" style="text-align: left; padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200);">รายละเอียดที่ต้องระบุในเนื้อหา</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">1. ปรัชญาและแนวคิด</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ปรัชญาการศึกษา (Educational Philosophy)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ปรัชญาการศึกษาของมหาวิทยาลัย/คณะ/หลักสูตร และแนวคิดการจัดการเรียนรู้ (เช่น OBE, Constructivism)</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">2. ข้อมูลทั่วไป</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ชื่อหลักสูตรและวุฒิการศึกษา</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ชื่อหลักสูตรและชื่อปริญญา ทั้งภาษาไทยและภาษาอังกฤษ (ตัวเต็มและตัวย่อ) และสาขาวิชา</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">3. เป้าหมายหลักสูตร</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ผลลัพธ์การเรียนรู้ (PLOs / ELOs)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">รายการ PLO ทุกข้อที่ชัดเจน วัดผลได้ และระบุความเชื่อมโยงกับความต้องการของผู้มีส่วนได้ส่วนเสีย (Stakeholders)</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">4. โครงสร้างหลักสูตร</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">โครงสร้างและแผนการศึกษา</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">จำนวนหน่วยกิตรวม, แบ่งหมวดวิชา (ศึกษาทั่วไป/วิชาเอก/เลือก), และแผนการเรียนรายเทอม (ปี 1–4)</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">5. รายละเอียดวิชา</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">คำอธิบายรายวิชา (Course Spec)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ชื่อวิชา, คำอธิบายรายวิชา, ผลลัพธ์การเรียนรู้ระดับรายวิชา (CLO), และ Curriculum Mapping (วิชานี้ตอบ PLO ข้อใด)</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">6. รูปแบบการเรียนสอน</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">วิธีการจัดการเรียนรู้</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">รูปแบบการสอนหลัก เช่น Active Learning, Project-based, CWIE, หรือการฝึกงานในสถานประกอบการ</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">7. การวัดและประเมินผล</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">เกณฑ์การประเมินและอุทธรณ์</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">วิธีการให้คะแนน, การใช้ Rubrics, นโยบายการสอบ, และขั้นตอนการอุทธรณ์ผลการเรียนที่ชัดเจน</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">8. เกณฑ์การจบ</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">เงื่อนไขการสำเร็จการศึกษา</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">เกณฑ์ GPA ขั้นต่ำ, คะแนนทดสอบภาษาอังกฤษ (เช่น TOEIC/IELTS), และข้อกำหนดพิเศษ (เช่น ผลงานตีพิมพ์)</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">9. บุคลากร</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ข้อมูลอาจารย์ประจำหลักสูตร</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">รายชื่อ, ตำแหน่งวิชาการ, คุณวุฒิการศึกษา, ความเชี่ยวชาญ, และผลงานวิจัย/วิชาการที่โดดเด่น</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">10. การสนับสนุน</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ระบบดูแลและสิ่งอำนวยความสะดวก</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ระบบอาจารย์ที่ปรึกษา, ทุนการศึกษา, ห้องปฏิบัติการ, ห้องสมุด, และซอฟต์แวร์ที่สนับสนุนการเรียน</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">11. ความสำเร็จ</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ผลลัพธ์และสถิติบัณฑิต</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">อัตราการได้งานทำ, เงินเดือนเริ่มต้นเฉลี่ย, รางวัลที่นักศึกษาได้รับ, และข่าวสารกิจกรรมล่าสุดของหลักสูตร</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">12. การติดต่อ</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ช่องทางการติดต่อสื่อสาร</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">เบอร์โทรศัพท์, อีเมล, Social Media (Facebook/Line), และแผนที่ตั้งของหลักสูตร/คณะ</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="font-size: 0.9rem; margin: 1.25rem 0 0.4rem; color: var(--color-gray-800);">เปรียบเทียบกับส่วนเดิมในระบบ — อะไรอยู่กรอบ 12 ข้อ อะไรควร “ตัดออก” จากการนับเป็นหัวข้อเนื้อหาหลักสูตร</h4>
                <p class="form-text text-muted" style="font-size: 0.8125rem; margin: 0 0 0.5rem;">คอลัมน์ “นอกกรอบ/แนวทาง” หมายถึง: ส่วนนั้น<strong>ไม่อยู่</strong>ใน 12 หัวข้อนี้โดยตรง หรือ<strong>ไม่ควรใช้แทน</strong>เนื้อหาตามข้อ 5–8 — ยัง<strong>เก็บฟีเจอร์ระบบได้</strong> แค่แยกบทบาท (เช่น สีเว็บ = ตั้งค่า มิใช่หัวข้อ 6)</p>
                <table class="program-section-legacy-table" style="width: 100%; min-width: 800px; border-collapse: collapse; font-size: 0.8125rem; line-height: 1.45; margin-bottom: 0.25rem;">
                    <thead>
                        <tr style="background: var(--color-gray-100);">
                            <th scope="col" style="text-align: left; padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); width: 28%;">ส่วนเดิมในระบบ (แอดมิน / หน้าเว็บ)</th>
                            <th scope="col" style="text-align: left; padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); width: 20%;">สอดคล้องหัวข้อ 12 ข้อ (เบอร์)</th>
                            <th scope="col" style="text-align: left; padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200);">นอกกรอบ หรือแนวจัด / “ตัด” อะไร</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ ข้อมูลพื้นฐาน + รูปหน้าปก</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">2</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">— ไม่ตัด</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ เนื้อหา &gt; 1. ภาพรวม (ปรัชญา วัตถุประสงค์ คุณลักษณะบัณฑิต)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">1, 3 (บางส่วน)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">— จัดรวมกับข้อ 1 ตามบทบาทจริง ไม่ตัดฟิลด์</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ 2 มาตรฐาน &amp; PLO / ELO</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">3</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">— ไม่ตัด</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ 3 แผนการเรียน + โครงสร้าง/แผน (HTML)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">4, 5–8 (รอง)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ตารางรายวิชาในระบบยัง<strong>ไม่เท่า</strong> course spec ครบ (CLO/mapping) — อย่า<strong>ตัด</strong> แต่เสริมข้อ 5 ในช่อง HTML หรือเอกสารดาวน์โหลด; ข้อ 6–8 ยัง<strong>ไม่มีฟิลด์เฉพาะ</strong> ใส่ในช่อง HTML/ไฟล์</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ 4 อาชีพ / ค่าใช้จ่าย / รับสมัคร / ติดต่อ + อัปโหลดแทรก + ลิงก์วิดีโอ</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">11, 12, 4 บางส่วน (ค่า/โครง)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>วิดีโอ</strong> ไม่นับแทน ข้อ 6/7/8; <strong>อาชีพ HTML</strong> กับ <strong>การ์ดอาชีพ</strong> บนหน้า (ถ้ามี) อย่าให้ซ้ำสาระโดยไม่จำเป็น</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ 5 เผยแพร่ &amp; SEO + Hero ในฟอร์มนี้</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">2, เชิงเทคนิค</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>ไม่นับ</strong> เป็น 6–8 — ลดงาน<strong>ซ้ำ</strong> รูป Hero กับแท็บพื้นฐาน ใช้แหล่งเดียวเท่าที่ทำได้</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ ดาวน์โหลด + เอกสารบนหน้า (QA)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">4, 5, 7, 10 รอง</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>นอก 12 ในฐานะ “หัวข้อ”</strong> เป็นคลังไฟล์ — ไม่<strong>แทน</strong>การเขียนในเว็บสำหรับข้อ 5/7/8 ถ้าต้องเผยแพร่ต่อสาธารณะ</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">ฐานข้อมูล/หน้า สิ่งอำนวยความสะดวก (Facilities)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">10</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">— ไม่ตัด; อาจเสริมทุน/อาจารย์ที่ปรึกษาในข้อความหรือเอกสาร</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ ศิษย์เก่าถึงรุ่นน้อง</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">11 บางส่วน / นอกกรอบ</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>ไม่อยู่</strong>ใน 1–8 ตามนิยาม AUN — อย่าใช้<strong>แทน</strong>สถิติ/กิจกรรมรายงานโครง; จัดเป็นเนื้อหา/ชุมชน<strong>เสริม</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ ข่าว + กิจกรรม (เพจแยก)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">11 เสริม</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>PR/ข่าว</strong> — ไม่นับ<strong>แทน</strong>เนื้อหา 3/5/6/7/8; <strong>“ตัด”</strong> เฉพาะความคาดหมายที่ข่าวจะรับรองมาตรฐานหลักสูตรแทนเอกสาร</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ บุคลากร (อ่านจากระบบ)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">9</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>ไม่ตัด</strong>; รายละเอียด CV/งานวิจัยเชิงลึกเสริมได้ในเนื้อหา/ดาวน์โหลด (ข้อ 9)</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">แท็บ การตั้งค่าเว็บ (สีธีม)</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">—</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>นอก 12 ข้อ</strong> — ตัด<strong>ออกจากบันทึก</strong>ว่า “เนื้อหาหลักสูตร” เป็นตั้งค่า UI</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">หน้าเว็บ: Hero, ปุ่มสมัคร, แถบ CTA, ลิงก์ภายนอก</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;">—</td>
                            <td style="padding: 0.5rem 0.65rem; border: 1px solid var(--color-gray-200); vertical-align: top;"><strong>ไม่นับ</strong>เป็นข้อ 1–12 — รับสมัคร/CTA แยกจากเอกสารหลักสูตร (ข้อ 4/8/12 เป็นสาระ ไม่ใช่เฉพาะลิงก์)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>

        <!-- Tab Content -->
        <div class="tab-content-container">
            <!-- Basic Info Tab -->
            <div id="basic-tab" class="tab-content active">
                <form id="basic-info-form" action="<?= base_url('program-admin/update/' . $program['id']) ?>" method="post" style="padding: 1.5rem;">
                    <?= csrf_field() ?>

                    <div class="form-section">
                        <h4 class="form-section-title">ข้อมูลพื้นฐาน</h4>
                        <p class="form-text text-muted program-edit-tab-hint" style="font-size: 0.875rem; margin-bottom: 1rem;">ชื่อหลักสูตร ระดับ และรูปหน้าปกด้านบน</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name_th" class="form-label">ชื่อหลักสูตร (ไทย) *</label>
                                <input type="text" id="name_th" name="name_th" class="form-control" value="<?= esc($program['name_th']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="name_en" class="form-label">ชื่อหลักสูตร (อังกฤษ)</label>
                                <input type="text" id="name_en" name="name_en" class="form-control" value="<?= esc($program['name_en']) ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="level" class="form-label">ระดับ *</label>
                                <select id="level" name="level" class="form-control" required>
                                    <option value="bachelor" <?= $program['level'] === 'bachelor' ? 'selected' : '' ?>>ปริญญาตรี</option>
                                    <option value="master" <?= $program['level'] === 'master' ? 'selected' : '' ?>>ปริญญาโท</option>
                                    <option value="doctorate" <?= $program['level'] === 'doctorate' ? 'selected' : '' ?>>ปริญญาเอก</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status" class="form-label">สถานะ *</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active" <?= $program['status'] === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                                    <option value="inactive" <?= $program['status'] === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                                </select>
                            </div>
                        </div>

                        <?php
                        $heroBasic = $program_page['hero_image'] ?? '';
                        $heroBasicUrl = '';
                        if ($heroBasic !== '') {
                            $heroBasicUrl = (strpos($heroBasic, 'http') === 0) ? $heroBasic : base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $heroBasic), '/'));
                        }
                        ?>
                        <div class="form-group hero-basic-wrap">
                            <label class="form-label">รูปหน้าปกหลักสูตร</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">รูปต้อนรับด้านบน แนะนำ 16:9</p>
                            <p class="admin-upload-hint" style="font-size: 0.8125rem; color: var(--color-gray-600); margin: 0 0 0.5rem;">รองรับ: JPG, PNG, WEBP, GIF</p>
                            <div id="hero-basic-preview" class="hero-basic-preview" style="<?= $heroBasicUrl ? '' : 'display:none;' ?> margin-bottom: 0.75rem;">
                                <img id="hero-basic-img" src="<?= esc($heroBasicUrl) ?>" alt="หน้าปกปัจจุบัน" style="max-width: 100%; max-height: 220px; width: auto; object-fit: contain; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                <div style="margin-top: 0.5rem;">
                                    <button type="button" id="hero-basic-remove" class="btn btn-outline btn-sm">ลบรูปหน้าปก</button>
                                </div>
                            </div>
                            <div id="hero-basic-drop" class="hero-basic-drop <?= $heroBasicUrl ? 'hero-basic-drop--hidden' : '' ?>">
                                <input type="file" id="hero-basic-file" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute;opacity:0;width:100%;height:100%;left:0;top:0;cursor:pointer;">
                                <span class="hero-basic-drop__text">ลากวางรูปที่นี่ หรือคลิกเพื่อเลือกไฟล์</span>
                                <span class="hero-basic-drop__hint">JPG, PNG, WEBP, GIF</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="basic-save-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกข้อมูล
                            </button>
                            <span id="basic-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Content Tab — แบ่งแท็บย่อยตาม Section -->
            <div id="content-tab" class="tab-content">
                <form id="content-page-form" action="<?= base_url('program-admin/update-page/' . $program['id']) ?>" method="post" enctype="multipart/form-data" style="padding: 0;">
                    <?= csrf_field() ?>
                    <?php
                    helper('program_page');
                    $ls_initial = parse_learning_standards_json($program_page['learning_standards_json'] ?? null);
                    ?>

                    <p class="content-tab-intro" style="margin: 0; padding: 0.75rem 1.5rem; font-size: 0.875rem; color: var(--color-gray-600); background: var(--color-gray-50); border-bottom: 1px solid var(--color-gray-200);">
                        แนะนำ: แท็บย่อยแยกหัวข้อ — บันทึกท้ายฟอร์มหรือบันทึกเฉพาะส่วนตามปุ่ม
                    </p>

                    <div class="content-subtab-bar" role="tablist" aria-label="แท็บย่อยเนื้อหาหลักสูตร">
                        <button type="button" class="content-subtab-btn active" data-content-sub="overview" role="tab" aria-selected="true" onclick="switchContentSubTab('overview')">1. ภาพรวม</button>
                        <button type="button" class="content-subtab-btn" data-content-sub="quality" role="tab" aria-selected="false" onclick="switchContentSubTab('quality')">2. มาตรฐาน &amp; PLO</button>
                        <button type="button" class="content-subtab-btn" data-content-sub="curriculum" role="tab" aria-selected="false" onclick="switchContentSubTab('curriculum')">3. แผนการเรียน</button>
                        <button type="button" class="content-subtab-btn" data-content-sub="pages" role="tab" aria-selected="false" onclick="switchContentSubTab('pages')">4. อาชีพ · รับสมัคร · ติดต่อ</button>
                        <button type="button" class="content-subtab-btn" data-content-sub="publish" role="tab" aria-selected="false" onclick="switchContentSubTab('publish')">5. เผยแพร่ &amp; หน้าเว็บ</button>
                    </div>

                    <div class="content-subtab-panels" style="padding: 1.5rem;">

                    <div id="content-sub-overview" class="content-subpanel active" role="tabpanel">
                        <?php helper('overview_lists');
                        $overview_objectives_init = overview_text_lines_from_db($program_page['objectives'] ?? null);
                        $overview_graduate_init    = overview_text_lines_from_db($program_page['graduate_profile'] ?? null);
                        ?>
                        <div class="form-section">
                            <h4 class="form-section-title">ภาพรวมหลักสูตร</h4>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">ปรัชญา (ย่อหน้า) · วัตถุประสงค์ · คุณลักษณะบัณฑิต — สองรายหลังกรอกทีละข้อ แสดงเป็นหมายเลขบนหน้าเว็บ (ข้อมูลเก่าหลายบรรทัดยังอ่านได้)</p>
                        <div class="form-group">
                            <label for="philosophy" class="form-label">ปรัชญาหลักสูตร</label>
                            <textarea id="philosophy" name="philosophy" class="form-control" rows="4" placeholder="ข้อความอธิบายปรัชญา"><?= esc($program_page['philosophy'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">วัตถุประสงค์ (ทีละข้อ)</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.5rem;">แต่ละข้อหนึ่งบรรทัด — บนหน้าเว็บแสดงเป็นรายการ 1. 2. 3.</p>
                            <div id="objectives-line-editor" class="ol-line-editor" data-initial="<?= htmlspecialchars(json_encode($overview_objectives_init, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"></div>
                            <button type="button" class="btn btn-outline btn-sm" id="objectives-line-add" style="margin-top:0.5rem">+ เพิ่มข้อ</button>
                            <textarea name="objectives" id="objectives" class="ol-serialized" hidden aria-hidden="true"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">คุณลักษณะบัณฑิต (ทีละข้อ)</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.5rem;">กำหนดทีละข้อ แสดงเป็นหมายเลขเช่นกัน</p>
                            <div id="graduate-line-editor" class="ol-line-editor" data-initial="<?= htmlspecialchars(json_encode($overview_graduate_init, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"></div>
                            <button type="button" class="btn btn-outline btn-sm" id="graduate-line-add" style="margin-top:0.5rem">+ เพิ่มข้อ</button>
                            <textarea name="graduate_profile" id="graduate_profile" class="ol-serialized" hidden aria-hidden="true"></textarea>
                        </div>
                        </div>
                    </div>

                    <div id="content-sub-quality" class="content-subpanel" role="tabpanel">
                        <div class="form-section">
                            <h4 class="form-section-title">มาตรฐานการเรียนรู้ &amp; PLO / ELO</h4>
                        <div class="form-group learning-standards-editor-wrap">
                            <label class="form-label">มาตรฐานการเรียนรู้ (Learning Standards)</label>
                            <div class="form-group">
                                <label for="learning-standards-intro" class="form-label">คำอธิบาย / ความสัมพันธ์ PLO กับมาตรฐาน (ไม่บังคับ)</label>
                                <textarea id="learning-standards-intro" class="form-control" rows="3" placeholder="เช่น หลักสูตรอ้างอิงมาตรฐานการเรียนรู้ของ... และกำหนด PLO ให้สอดคล้องกับ..."><?= esc($ls_initial['intro'] ?? '') ?></textarea>
                            </div>
                            <label class="form-label" style="margin-top: 0.75rem;">รายการมาตรฐานการเรียนรู้</label>
                            <div id="learning-standards-list" class="learning-standards-list" data-initial="<?= htmlspecialchars(json_encode($ls_initial['standards'] ?? [], JSON_UNESCAPED_UNICODE)) ?>"></div>
                            <div class="elos-actions" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn btn-outline btn-sm" id="ls-add-btn">+ เพิ่มมาตรฐาน</button>
                                <button type="button" class="btn btn-primary btn-sm" id="ls-save-ajax-btn">บันทึกมาตรฐานการเรียนรู้</button>
                                <span id="ls-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                            <label class="form-label" style="margin-top: 1rem;">ตารางเชื่อมโยงมาตรฐาน – PLO (ไม่บังคับ)</label>
                            <div id="learning-standards-mapping-list" class="learning-standards-mapping-list" data-initial="<?= htmlspecialchars(json_encode($ls_initial['mapping'] ?? [], JSON_UNESCAPED_UNICODE)) ?>"></div>
                            <div class="elos-actions" style="margin-top: 0.5rem;">
                                <button type="button" class="btn btn-outline btn-sm" id="ls-add-mapping-btn">+ เพิ่มแถวเชื่อมโยง</button>
                            </div>
                            <textarea id="learning_standards_json" name="learning_standards_json" class="form-control" style="display: none;" aria-hidden="true"><?= esc($program_page['learning_standards_json'] ?? '') ?></textarea>
                        </div>

                        <?php
                        $elos_initial = [];
                        if (!empty($program_page['elos_json'])) {
                            $decoded = json_decode($program_page['elos_json'], true);
                            if (is_array($decoded)) { $elos_initial = $decoded; }
                        }
                        ?>
                        <div class="form-group elos-editor-wrap">
                            <label class="form-label">PLO / ELO (ผลลัพธ์การเรียนรู้ระดับหลักสูตร)</label>
                            <div id="elos-list" class="elos-list" data-initial="<?= htmlspecialchars(json_encode($elos_initial, JSON_UNESCAPED_UNICODE)) ?>"></div>
                            <div class="elos-actions" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn btn-outline btn-sm" id="elos-add-btn">+ เพิ่ม ELO</button>
                                <button type="button" class="btn btn-primary btn-sm" id="elos-save-ajax-btn">บันทึก ELO</button>
                                <span id="elos-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                            <textarea id="elos_json" name="elos_json" class="form-control" style="display: none;" aria-hidden="true"><?= esc($program_page['elos_json'] ?? '') ?></textarea>
                        </div>
                        </div>
                    </div>

                    <div id="content-sub-curriculum" class="content-subpanel" role="tabpanel">
                        <div class="form-section">
                            <h4 class="form-section-title">แผนการเรียน / รายวิชา &amp; ข้อความโครงสร้าง</h4>
                        <?php
                        $curriculum_initial = [];
                        if (!empty($program_page['curriculum_json'])) {
                            $decoded = json_decode($program_page['curriculum_json'], true);
                            if (is_array($decoded)) { $curriculum_initial = $decoded; }
                        }
                        ?>
                        <div class="form-group curriculum-editor-wrap">
                            <label class="form-label">รายวิชาโครงสร้างหลักสูตร (แยกตามปี/ภาค)</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.75rem;">เพิ่มปี → ภาคเรียน → รายวิชา (หน่วยกิต) ใช้แสดงตารางบนหน้าเว็บ — คนละส่วนกับกล่อง &quot;แผนการเรียน (คำอธิบาย)&quot; ด้านล่าง</p>
                            <div id="curriculum-list" class="curriculum-list" data-initial="<?= htmlspecialchars(json_encode($curriculum_initial, JSON_UNESCAPED_UNICODE)) ?>"></div>
                            <div class="curriculum-actions" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn btn-outline btn-sm" id="curriculum-add-year-btn">+ เพิ่มปี</button>
                                <button type="button" class="btn btn-primary btn-sm" id="curriculum-save-ajax-btn">บันทึกแผนการเรียน</button>
                                <span id="curriculum-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                            <textarea id="curriculum_json" name="curriculum_json" class="form-control" style="display: none;" aria-hidden="true"><?= esc($program_page['curriculum_json'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">โครงสร้างหลักสูตร</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.75rem;">เพิ่มเป็นหัวข้อย่อยและรายละเอียด — กรอกข้อความธรรมดาได้ แทรกรูป/ลิงก์ที่ช่องรายละเอียด (คลิกในช่องก่อน แล้วใช้อัปโหลดแทรกหรือปุ่มด้านล่าง)</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรก (โครงสร้าง)" style="margin-bottom: 0.5rem;">
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="curriculum_structure" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="curriculum_structure" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="curriculum_structure" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="curriculum_structure" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="curriculum_structure" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <div id="ptb-wrap-curriculum_structure" class="ptb-editor-wrap" data-ptb-kind="curriculum"></div>
                            <button type="button" class="btn btn-outline btn-sm" id="ptb-add-curriculum_structure" style="margin-top:0.5rem">+ เพิ่มหัวข้อ</button>
                            <textarea id="curriculum_structure" name="curriculum_structure" class="ptb-serialized-field" hidden aria-hidden="true"><?= esc($program_page['curriculum_structure'] ?? '') ?></textarea>
                            <div style="margin-top: 0.5rem;">
                                <button type="button" class="btn btn-primary btn-sm" id="curriculum-structure-save-ajax-btn">บันทึกโครงสร้างหลักสูตร</button>
                                <span id="curriculum-structure-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">แผนการเรียน (คำอธิบาย)</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.75rem;">อธิบายแนวทาง/ลำดับการเรียน (แยกจากตารางรายวิชารายปีด้านบน)</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรก (แผนการเรียน)" style="margin-bottom: 0.5rem;">
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="study_plan" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="study_plan" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="study_plan" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="study_plan" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm ptb-insert-btn" data-ptb-field="study_plan" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <div id="ptb-wrap-study_plan" class="ptb-editor-wrap" data-ptb-kind="study"></div>
                            <button type="button" class="btn btn-outline btn-sm" id="ptb-add-study_plan" style="margin-top:0.5rem">+ เพิ่มหัวข้อ</button>
                            <textarea id="study_plan" name="study_plan" class="ptb-serialized-field" hidden aria-hidden="true"><?= esc($program_page['study_plan'] ?? '') ?></textarea>
                        </div>
                        </div>
                    </div>

                    <div id="content-sub-pages" class="content-subpanel" role="tabpanel">
                        <div class="form-section">
                            <h4 class="form-section-title">อาชีพ · ค่าใช้จ่าย · รับสมัคร · ติดต่อ · วิดีโอ</h4>

                        <?php helper('career_cards'); ?>
                        <div class="form-group career-cards-admin">
                            <label class="form-label">อาชีพที่สามารถประกอบได้ (การ์ด)</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.75rem;">เพิ่มทีละอาชีพ — ชื่อ คำอธิบายสั้น และไอคอน (แสดงเป็นบัตรบนหน้าเว็บ)</p>
                            <div id="career-cards-editor" class="career-cards-editor"></div>
                            <button type="button" class="btn btn-outline btn-sm" id="career-card-add-btn" style="margin-top: 0.5rem;">+ เพิ่มอาชีพ</button>
                            <textarea name="careers_json" id="careers_json" style="display: none !important;" aria-hidden="true"><?= htmlspecialchars($program_page['careers_json'] ?? '[]', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="form-group tuition-fees-items-admin">
                            <label class="form-label">ค่าเล่าเรียน/ค่าธรรมเนียม (รายการ)</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.75rem;">เพิ่มทีละรายการ — ชื่อรายการ จำนวนเงินหรือข้อความกำหนด หมายเหตุ (ไม่บังคับ)</p>
                            <div id="tuition-fees-editor"></div>
                            <button type="button" class="btn btn-outline btn-sm" id="tuition-fee-add-btn" style="margin-top: 0.5rem;">+ เพิ่มรายการ</button>
                            <textarea name="tuition_fees_json" id="tuition_fees_json" style="display: none !important;" aria-hidden="true"><?= htmlspecialchars($program_page['tuition_fees_json'] ?? '[]', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="admission_info" class="form-label">การรับสมัคร</label>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="admission_info" name="admission_info" class="form-control" rows="4"><?= esc($program_page['admission_info'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="contact_info" class="form-label">ข้อมูลติดต่อ</label>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="contact_info" name="contact_info" class="form-control" rows="4"><?= esc($program_page['contact_info'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="intro_video_url" class="form-label">วิดีโอแนะนำ</label>
                            <input type="url" id="intro_video_url" name="intro_video_url" class="form-control" value="<?= esc($program_page['intro_video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=...">
                        </div>
                        </div>
                    </div>

                    <div id="content-sub-publish" class="content-subpanel" role="tabpanel">
                        <div class="form-section">
                            <h4 class="form-section-title">เผยแพร่ &amp; หน้าเว็บหลักสูตร</h4>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">การแสดงผล SEO และรูป Hero</p>
                        <div class="form-section" style="margin-top: 0;">
                            <h5 class="form-section-title" style="font-size: 1rem;">รูปหน้าปก (Hero) สำหรับบันทึกรวม</h5>
                            <div class="form-group">
                                <label class="form-label">รูปหน้าปกหลักสูตร (Hero)</label>
                                <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แนะนำกว้างประมาณ 1920px</p>
                                <?php
                                $hero = $program_page['hero_image'] ?? '';
                                $heroUrl = '';
                                if ($hero !== '') {
                                    $heroUrl = (strpos($hero, 'http') === 0) ? $hero : base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $hero), '/'));
                                }
                                ?>
                                <?php if ($heroUrl !== ''): ?>
                                <div class="hero-preview-wrap" style="margin-bottom: 0.75rem;">
                                    <img id="hero-preview-img" src="<?= esc($heroUrl) ?>" alt="หน้าปกปัจจุบัน" style="max-width: 100%; max-height: 200px; object-fit: contain; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                </div>
                                <?php else: ?>
                                <div class="hero-preview-wrap" style="margin-bottom: 0.75rem; display: none;">
                                    <img id="hero-preview-img" src="" alt="" style="max-width: 100%; max-height: 200px; object-fit: contain; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                </div>
                                <?php endif; ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                    <input type="file" id="hero_image" name="hero_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" style="max-width: 280px;">
                                    <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                        <input type="checkbox" name="hero_image_remove" value="1" id="hero_image_remove"> ลบรูปหน้าปก
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="is_published" class="form-label">สถานะการเผยแพร่</label>
                                <select id="is_published" name="is_published" class="form-control">
                                    <option value="0" <?= ($program_page['is_published'] ?? 0) == 0 ? 'selected' : '' ?>>ยังไม่เผยแพร่</option>
                                    <option value="1" <?= ($program_page['is_published'] ?? 0) == 1 ? 'selected' : '' ?>>เผยแพร่แล้ว</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description" class="form-label">คำอธิบายสำหรับ SEO</label>
                            <textarea id="meta_description" name="meta_description" class="form-control" rows="2" placeholder="คำอธิบายสำหรับแสดงในผลการค้นหา"><?= esc($program_page['meta_description'] ?? '') ?></textarea>
                        </div>
                        </div>
                    </div>

                    </div><!-- /.content-subtab-panels -->

                        <div class="form-actions" style="padding: 1rem 1.5rem 1.5rem; border-top: 1px solid var(--color-gray-200); background: var(--color-gray-50);">
                            <button type="submit" class="btn btn-primary" id="content-save-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกเนื้อหา
                            </button>
                            <span id="content-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                        </div>
                </form>
            </div>

            <!-- ศิษย์เก่าถึงรุ่นน้อง Tab -->
            <?php
            $alumni_initial = [];
            if (!empty($program_page['alumni_messages_json'])) {
                $decoded = json_decode($program_page['alumni_messages_json'], true);
                if (is_array($decoded)) {
                    $alumni_initial = $decoded;
                }
            }
            ?>
            <div id="alumni-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="form-section">
                        <h4 class="form-section-title">ศิษย์เก่าถึงรุ่นน้อง</h4>
                        <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">แสดงในส่วนศิษย์เก่าถึงรุ่นน้อง</p>
                        <div id="alumni-list" class="alumni-list" data-initial="<?= htmlspecialchars(json_encode($alumni_initial, JSON_UNESCAPED_UNICODE)) ?>"></div>
                        <div class="alumni-actions" style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                            <button type="button" class="btn btn-outline btn-sm" id="alumni-add-btn">+ เพิ่มคน</button>
                            <button type="button" class="btn btn-primary btn-sm" id="alumni-save-ajax-btn">บันทึกศิษย์เก่าถึงรุ่นน้อง</button>
                            <span id="alumni-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Downloads Tab -->
            <div id="downloads-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1.5rem;">
                        <h4>เอกสารดาวน์โหลด</h4>
                        <p style="color: var(--color-gray-600);">เอกสารให้นักศึกษาโหลด — นำลิงก์ไปวางในเนื้อหาได้</p>
                    </div>
                    <p class="admin-upload-hint" style="font-size: 0.875rem; color: var(--color-gray-600); margin: -0.5rem 0 1rem;">รองรับ: PDF, Word (doc/docx), Excel, PowerPoint, Zip, รูป (JPG/PNG/GIF), MP4, MP3, TXT ตามที่ระบบอนุญาต</p>

                    <!-- Upload Form -->
                    <form action="<?= base_url('program-admin/upload-download/' . $program['id']) ?>" method="post" enctype="multipart/form-data" class="admin-upload-panel" style="margin-bottom: 2rem;">
                        <?= csrf_field() ?>
                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label for="title" class="form-label">ชื่อไฟล์ *</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="file_type" class="form-label">ประเภทไฟล์ *</label>
                                <select id="file_type" name="file_type" class="form-control" required>
                                    <option value="">-- เลือก --</option>
                                    <option value="pdf">PDF</option>
                                    <option value="doc">Word</option>
                                    <option value="docx">Word</option>
                                    <option value="xlsx">Excel</option>
                                    <option value="pptx">PowerPoint</option>
                                    <option value="zip">ZIP</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="file" class="form-label">ไฟล์ *</label>
                            <input type="file" id="file" name="file" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            อัปโหลดไฟล์
                        </button>
                    </form>

                    <!-- Downloads List -->
                    <?php if (!empty($downloads)): ?>
                        <div class="downloads-list">
                            <?php foreach ($downloads as $download): ?>
                                <div class="download-item" style="display: flex; align-items: center; padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px; margin-bottom: 0.5rem;">
                                    <div class="file-icon" style="margin-right: 1rem;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-500);">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="16" y1="13" x2="8" y2="13" />
                                            <line x1="16" y1="17" x2="8" y2="17" />
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500;"><?= esc($download['title']) ?></div>
                                        <div style="font-size: 0.875rem; color: var(--color-gray-600);">
                                            <?= $programDownloadModel->getFormattedSize($download['file_size']) ?> •
                                            <span style="text-transform: uppercase;"><?= esc($download['file_type']) ?></span>
                                        </div>
                                    </div>
                                    <div class="actions">
                                        <a href="<?= (strpos($download['file_path'], 'uploads/') === 0 ? base_url('serve/' . $download['file_path']) : base_url('serve/uploads/' . $download['file_path'])) ?>" class="btn btn-outline btn-sm" target="_blank">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                                <polyline points="7 10 12 15 17 10" />
                                                <line x1="12" y1="15" x2="12" y2="3" />
                                            </svg>
                                            ดาวน์โหลด
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('<?= base_url('program-admin/delete-download/' . $download['id']) ?>')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                            </svg>
                                            ลบ
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <p>ยังไม่มีไฟล์ดาวน์โหลด</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- News Tab -->
            <div id="news-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1.5rem;">
                        <h4>ข่าวหลักสูตร</h4>
                        <p style="color: var(--color-gray-600);">ข่าวที่เชื่อมกับหลักสูตรนี้</p>
                    </div>
                    <div id="program-news-list" style="margin-bottom: 2rem;">
                        <p style="color: var(--color-gray-500);">กำลังโหลด...</p>
                    </div>
                    <hr style="margin: 2rem 0;">

                    <style>
                    .news-form-container { font-family: 'Sarabun', 'Prompt', sans-serif; color: #334155; background: #f8fafc; padding: 2rem; border-radius: 12px; }
                    .news-form-container .news-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                    .news-form-container .news-card-title { font-size: 1.1rem; font-weight: 600; color: #0f172a; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.5rem; }
                    .news-form-container .news-card-title .card-title-icon { flex-shrink: 0; color: #475569; }
                    .news-form-container input[type="file"] { width: 100%; padding: 0.75rem 1rem; font-size: 0.9375rem; color: #334155; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
                    .news-form-container input[type="file"]:focus { border-color: #3b82f6; outline: 0; box-shadow: 0 0 0 3px rgba(59,130,246,0.25); }
                    .news-form-container .form-group { margin-bottom: 1.25rem; }
                    .news-form-container .form-group:last-child { margin-bottom: 0; }
                    .news-form-container .form-label { display: block; font-weight: 500; margin-bottom: 0.5rem; color: #475569; }
                    .news-form-container .text-danger { color: #ef4444; }
                    .news-form-container .form-control { width: 100%; padding: 0.75rem 1rem; font-size: 1rem; line-height: 1.5; color: #334155; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; transition: border-color 0.15s, box-shadow 0.15s; box-sizing: border-box; }
                    .news-form-container .form-control:focus { border-color: #3b82f6; outline: 0; box-shadow: 0 0 0 3px rgba(59,130,246,0.25); }
                    .news-form-container .form-row { display: flex; gap: 1.5rem; flex-wrap: wrap; }
                    .news-form-container .form-row .form-group { flex: 1; min-width: 250px; }
                    .news-form-container .file-upload-info { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
                    .news-form-container .checkbox-wrapper { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.5rem 0; }
                    .news-form-container .checkbox-wrapper input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
                    .news-form-container .btn-submit { background: #2563eb; color: #fff; font-weight: 600; padding: 0.75rem 2rem; border: none; border-radius: 6px; cursor: pointer; transition: background-color 0.2s; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; }
                    .news-form-container .btn-submit:hover { background: #1d4ed8; }
                    .news-form-container .btn-cancel { background: #f1f5f9; color: #334155; padding: 0.75rem 1.5rem; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-size: 1rem; }
                    .news-form-container .btn-cancel:hover { background: #e2e8f0; }
                    </style>

                    <div class="news-form-container">
                        <div style="margin-bottom: 2rem;">
                            <h3 style="margin: 0 0 0.5rem 0; color: #0f172a;">สร้างข่าวหลักสูตรใหม่</h3>
                            <p style="color: #64748b; margin: 0;">เพิ่มเนื้อหาและประกาศสำหรับนักศึกษาในหลักสูตร</p>
                        </div>

                        <form action="<?= base_url('program-admin/news/' . $program['id'] . '/create') ?>" method="post" enctype="multipart/form-data" id="program-news-form">
                            <?= csrf_field() ?>
                            <div class="news-card">
                                <div class="news-card-title">
                                    <svg class="card-title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    ข้อมูลทั่วไป
                                </div>
                                <div class="form-group">
                                    <label for="news_title" class="form-label">หัวข้อข่าว <span class="text-danger">*</span></label>
                                    <input type="text" id="news_title" name="title" class="form-control" required minlength="3" maxlength="500" placeholder="เช่น ประกาศรับสมัครนักศึกษา ปีการศึกษา 2567">
                                </div>
                                <div class="form-group">
                                    <label for="news_content" class="form-label">เนื้อหาข่าว <span class="text-danger">*</span></label>
                                    <textarea id="news_content" name="content" class="form-control" rows="8" required placeholder="ใส่รายละเอียดข่าวที่นี่..."></textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="news_excerpt" class="form-label">สรุปเนื้อหาสั้นๆ (ไม่บังคับ)</label>
                                    <textarea id="news_excerpt" name="excerpt" class="form-control" rows="2" placeholder="ข้อความสรุปที่จะแสดงในหน้าแรก (ถ้าไม่ใส่ ระบบจะดึงจากเนื้อหาบางส่วน)"></textarea>
                                </div>
                            </div>
                            <div class="news-card">
                                <div class="news-card-title">
                                    <svg class="card-title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-1.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h1.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v1.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-1.09a1.65 1.65 0 00-1.51 1z"/></svg>
                                    การแสดงผล
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="news_status" class="form-label">สถานะการเผยแพร่ <span class="text-danger">*</span></label>
                                        <select id="news_status" name="status" class="form-control" required>
                                            <option value="draft">ร่าง (ยังไม่แสดงผล)</option>
                                            <option value="published" selected>เผยแพร่ทันที</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label">ประเภทกิจกรรม</label>
                                        <label class="checkbox-wrapper">
                                            <input type="checkbox" name="display_as_event" value="1">
                                            <span>แสดงข่าวนี้เป็น <strong>"กิจกรรมที่จะเกิดขึ้น"</strong> (Upcoming Event)</span>
                                        </label>
                                    </div>
                                    <div class="form-group" style="flex: 1 1 100%; margin-bottom: 0;">
                                        <label for="program_news_published_at" class="form-label">วันและเวลาประกาศ</label>
                                        <input type="datetime-local" id="program_news_published_at" name="published_at" class="form-control" style="max-width: 22rem;">
                                        <div class="file-upload-info">เมื่อเผยแพร่ — ว่าง = ใช้เวลาปัจจุบัน</div>
                                    </div>
                                </div>
                            </div>
                            <div class="news-card" style="margin-bottom: 2rem;">
                                <div class="news-card-title">
                                    <svg class="card-title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                    สื่อและไฟล์แนบ
                                </div>
                                <div class="form-group">
                                    <label for="news_featured_image" class="form-label">ภาพปกข่าว (Featured Image)</label>
                                    <div class="featured-image-box program-news-featured-box" id="programNewsFeaturedBox" role="button" tabindex="0" aria-label="เลือกภาพปก" style="min-height: 120px; border: 2px dashed var(--color-gray-300); border-radius: 8px; padding: 1rem; cursor: pointer; text-align: center;">
                                        <div id="programNewsFeaturedPlaceholder">
                                            <p style="margin: 0; color: var(--color-gray-600);">คลิกหรือลากวางเพื่อเลือกภาพ แล้วตัด (crop) ตามต้องการ</p>
                                            <small style="color: var(--color-gray-500);">JPG, PNG, WEBP — อัตราส่วน 16:9</small>
                                        </div>
                                    </div>
                                    <input type="file" id="news_featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control" style="display: none;">
                                    <input type="hidden" name="featured_image_base64" id="program_news_featured_image_base64" value="">
                                    <div class="file-upload-info">รองรับไฟล์: JPG, PNG, WEBP (แนะนำขนาด 1200x630px) — สามารถตัดภาพก่อนบันทึกได้</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">รูปภาพประกอบเพิ่มเติม (เลือกได้หลายไฟล์)</label>
                                    <input type="file" name="attachments_images[]" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control" multiple>
                                    <div class="file-upload-info">สร้างเป็นแกลลอรี่ภาพด้านล่างข่าว</div>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">เอกสารแนบ (เลือกได้หลายไฟล์)</label>
                                    <input type="file" name="attachments_docs[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="form-control" multiple>
                                    <div class="file-upload-info">รองรับไฟล์: PDF, Word, Excel, PowerPoint</div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <button type="button" class="btn-cancel" onclick="window.history.back()">ยกเลิก</button>
                                <button type="submit" class="btn-submit" style="margin-left: 1rem;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    บันทึกข่าว
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Personnel Tab -->
            <div id="personnel-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1.5rem;">
                        <h4>บุคลากรหลักสูตร</h4>
                        <p style="color: var(--color-gray-600);">คณาจารย์ประจำหลักสูตร (ข้อมูลจากระบบ)</p>
                    </div>

                    <!-- Coordinator -->
                    <?php if ($coordinator): ?>
                        <div class="personnel-card" style="border: 1px solid var(--color-blue-200); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; background: var(--color-blue-50);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="person-avatar" style="width: 64px; height: 64px; background: var(--color-blue-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-blue-600);">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 style="margin: 0; color: var(--color-blue-700);">ประธานหลักสูตร</h5>
                                    <p style="margin: 0.25rem 0 0 0; font-weight: 600;"><?= esc($coordinator['name']) ?></p>
                                    <p style="margin: 0; color: var(--color-gray-600); font-size: 0.875rem;"><?= esc($coordinator['position'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Other Personnel -->
                    <?php if (!empty($personnel_list)): ?>
                        <h5 style="margin-bottom: 1rem;">อาจารย์ประจำหลักสูตร</h5>
                        <div class="personnel-grid" style="display: grid; gap: 1rem;">
                            <?php foreach ($personnel_list as $personnel): ?>
                                <div class="personnel-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                    <div class="person-avatar" style="width: 48px; height: 48px; background: var(--color-gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <p style="margin: 0; font-weight: 500;"><?= esc($personnel['name']) ?></p>
                                        <p style="margin: 0; color: var(--color-gray-600); font-size: 0.875rem;"><?= esc($personnel['position'] ?? '') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                <path d="M16 3.13a4 4 0 010 7.75" />
                            </svg>
                            <p>ยังไม่มีข้อมูลบุคลากร</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Website Settings Tab -->
            <div id="website-tab" class="tab-content">
                <form action="<?= base_url('program-admin/update-website/' . $program['id']) ?>" method="post" style="padding: 1.5rem;">
                    <?= csrf_field() ?>
                    <div class="form-section">
                        <h4 class="form-section-title">การตั้งค่าเว็บไซต์หลักสูตร</h4>
                        <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">สีของหน้าเว็บหลักสูตร</p>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="theme_color_hex" class="form-label">สีธีมหลักสูตร</label>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <input type="color" id="theme_color" class="form-control" value="<?= esc($program_page['theme_color'] ?? '#1e40af') ?>" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                <input type="text" id="theme_color_hex" name="theme_color" class="form-control" value="<?= esc($program_page['theme_color'] ?? '#1e40af') ?>" style="width: 100px; font-family: monospace;" maxlength="7" placeholder="#1e40af">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="text_color" class="form-label">สีข้อความ</label>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="color" id="text_color" class="form-control" value="<?= esc($program_page['text_color'] ?? '#1e293b') ?>" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                    <input type="text" id="text_color_hex" name="text_color" class="form-control" value="<?= esc($program_page['text_color'] ?? '') ?>" style="width: 100px; font-family: monospace;" maxlength="7" placeholder="#1e293b">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="var h=document.getElementById('text_color_hex'); var c=document.getElementById('text_color'); h.value=''; c.value='#1e293b';" title="ใช้ค่าตั้งต้น">ล้าง</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="background_color" class="form-label">สีพื้นหลัง</label>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="color" id="background_color" class="form-control" value="<?= esc($program_page['background_color'] ?? '#f8fafc') ?>" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                    <input type="text" id="background_color_hex" name="background_color" class="form-control" value="<?= esc($program_page['background_color'] ?? '') ?>" style="width: 100px; font-family: monospace;" maxlength="7" placeholder="#f8fafc">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="var h=document.getElementById('background_color_hex'); var c=document.getElementById('background_color'); h.value=''; c.value='#f8fafc';" title="ใช้ค่าตั้งต้น">ล้าง</button>
                                </div>
                            </div>
                        </div>

                        <p class="form-text text-muted" style="font-size: 0.8125rem; margin-top: 0.5rem;">เว้นว่างเพื่อใช้ค่าตั้งต้นของระบบ</p>

                        <div class="form-actions" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกการตั้งค่าเว็บไซต์
                            </button>
                        </div>
                    </div>
                </form>

                <!-- นำเข้า / ส่งออก JSON — 3 namespace: ข้อมูลพื้นฐาน + เนื้อหาหลักสูตร + การตั้งค่า -->
                <div class="program-bundle-panel" style="margin: 0 1.5rem 1.5rem; padding: 1rem 1.25rem; border: 1px solid var(--color-gray-200); border-radius: 8px; background: #fff; font-size: 0.875rem;">
                    <h4 class="form-section-title" style="margin: 0 0 0.5rem 0; font-size: 1rem;">นำเข้า / ส่งออก JSON (ข้อมูลพื้นฐาน · เนื้อหาหลักสูตร · การตั้งค่า)</h4>
                    <p class="form-text text-muted" style="margin: 0 0 0.75rem 0; font-size: 0.8125rem;">เอกสาร JSON ครอบ 3 ส่วน (<code>basic</code>/<code>content</code>/<code>settings</code>) — single source per field ไม่มีข้อมูลซ้ำ. ไฟล์ต้องระบุ <code>schema_version</code> กับ <code>program_id</code> ให้ตรงหลักสูตรนี้ (<?= (int) $program['id'] ?>). หลังส่งออกหรือหลังนำเข้าสำเร็จ ระบบบันทึกสำเนาไว้ที่ <code style="font-size:0.75rem;">writable/uploads/programs/<?= (int) $program['id'] ?>/data/content-bundle-latest.json</code> (แหล่งความจริงยังเป็นฐานข้อมูล — รองรับนำเข้ารูปแบบเดิม <code>{program, page}</code> อัตโนมัติ)</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; margin-bottom: 0.75rem;">
                        <a href="<?= base_url('program-admin/bundle-export/' . (int) $program['id']) ?>" class="btn btn-outline btn-sm" download>ดาวน์โหลด JSON ปัจจุบัน</a>
                        <a href="<?= base_url('program-admin/bundle-template/' . (int) $program['id']) ?>" class="btn btn-outline btn-sm" download>ดาวน์โหลดแม่แบบว่าง</a>
                        <button type="button" class="btn btn-outline btn-sm" id="bundle-preview-current-btn">ดูสรุปฐานปัจจุบันต่อหัวข้อ</button>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="bundle-file-input" class="form-label" style="font-size: 0.8125rem;">นำเข้าไฟล์ .json</label>
                            <input type="file" id="bundle-file-input" accept=".json,application/json" class="form-control" style="max-width: 22rem; font-size: 0.875rem;">
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="bundle-import-preview-btn">ตรวจก่อนนำเข้า</button>
                    </div>
                    <p id="bundle-import-msg" class="ajax-msg" style="min-height: 1.2em; margin: 0 0 0.5rem 0;" aria-live="polite"></p>
                    <ul id="bundle-import-errors" class="bundle-error-list" aria-live="polite" style="display: none; margin: 0 0 0.5rem 0; padding: 0.5rem 0.75rem 0.5rem 1.25rem; border: 1px solid var(--color-error, #c92a2a); border-radius: 6px; background: #fff5f5; color: var(--color-error, #c92a2a); font-size: 0.8125rem; list-style: disc;"></ul>
                    <div id="bundle-preview-wrap" style="display: none; margin-top: 0.5rem; max-height: 420px; overflow: auto; border: 1px solid var(--color-gray-200); border-radius: 8px; padding: 0.75rem; background: var(--color-gray-50);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8125rem;" id="bundle-compare-grid">
                        </div>
                    </div>
                    <div id="bundle-commit-row" style="display: none; margin-top: 0.5rem;">
                        <button type="button" class="btn btn-primary btn-sm" id="bundle-import-commit-btn">ยืนยันบันทึกลงฐานข้อมูล</button>
                        <span class="form-text text-muted" style="font-size: 0.75rem; margin-left: 0.5rem;">token อายุ ~10 นาที — บันทึกครอบทั้ง <code>programs</code> + <code>program_pages</code> ใน transaction เดียว</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ครอปรูปหน้าปก -->
<div id="hero-crop-modal" class="hero-crop-modal" role="dialog" aria-labelledby="hero-crop-modal-title" aria-modal="true" style="display:none;">
    <div class="hero-crop-modal__backdrop"></div>
    <div class="hero-crop-modal__box">
        <div class="hero-crop-modal__header">
            <h3 id="hero-crop-modal-title" class="hero-crop-modal__title">ครอปภาพหน้าปก</h3>
            <p class="hero-crop-modal__subtitle">ปรับกรอบให้พอดีกับภาพหน้าปก (อัตราส่วน 16:9)</p>
            <button type="button" class="hero-crop-modal__close" id="hero-crop-close" aria-label="ปิด">&times;</button>
        </div>
        <div class="hero-crop-modal__body">
            <div class="hero-crop-container">
                <img id="hero-crop-image" src="" alt="">
            </div>
        </div>
        <div class="hero-crop-modal__footer">
            <button type="button" class="btn btn-outline" id="hero-crop-cancel">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="hero-crop-confirm">
                <span class="hero-crop-confirm-text">ตกลง ใช้รูปนี้</span>
                <span class="hero-crop-confirm-loading" style="display:none;">กำลังอัปโหลด...</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal ครอปรูปโปรไฟล์ศิษย์เก่า (1:1) -->
<div id="alumni-crop-modal" class="hero-crop-modal" role="dialog" aria-labelledby="alumni-crop-modal-title" aria-modal="true" style="display:none;">
    <div class="hero-crop-modal__backdrop"></div>
    <div class="hero-crop-modal__box">
        <div class="hero-crop-modal__header">
            <h3 id="alumni-crop-modal-title" class="hero-crop-modal__title">ครอปรูปโปรไฟล์ศิษย์เก่า</h3>
            <p class="hero-crop-modal__subtitle">ปรับกรอบรูปโปรไฟล์ (อัตราส่วน 1:1)</p>
            <button type="button" class="hero-crop-modal__close" id="alumni-crop-close" aria-label="ปิด">&times;</button>
        </div>
        <div class="hero-crop-modal__body">
            <div class="hero-crop-container">
                <img id="alumni-crop-image" src="" alt="">
            </div>
        </div>
        <div class="hero-crop-modal__footer">
            <button type="button" class="btn btn-outline" id="alumni-crop-cancel">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="alumni-crop-confirm">
                <span class="alumni-crop-confirm-text">ตกลง ใช้รูปนี้</span>
                <span class="alumni-crop-confirm-loading" style="display:none;">กำลังอัปโหลด...</span>
            </button>
        </div>
    </div>
</div>

<!-- โมดัล crop ภาพปกข่าวหลักสูตร (เหมือน admin/news) -->
<div id="program-news-featured-crop-modal" class="hero-crop-modal" role="dialog" aria-labelledby="program-news-featured-crop-title" aria-modal="true" style="display:none;">
    <div class="hero-crop-modal__backdrop"></div>
    <div class="hero-crop-modal__box">
        <div class="hero-crop-modal__header">
            <h3 id="program-news-featured-crop-title" class="hero-crop-modal__title">ตัดภาพปกข่าว</h3>
            <p class="hero-crop-modal__subtitle">ปรับกรอบภาพปก (อัตราส่วน 16:9)</p>
            <button type="button" class="hero-crop-modal__close" id="program-news-featured-crop-close" aria-label="ปิด">&times;</button>
        </div>
        <div class="hero-crop-modal__body">
            <div class="hero-crop-container">
                <img id="program-news-featured-crop-image" src="" alt="">
            </div>
        </div>
        <div class="hero-crop-modal__footer">
            <button type="button" class="btn btn-outline" id="program-news-featured-crop-cancel">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="program-news-featured-crop-confirm">ตัดและใช้ภาพ</button>
        </div>
    </div>
</div>

<style>
.hero-basic-drop {
    position: relative;
    border: 2px dashed var(--color-gray-300);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    background: var(--color-gray-50);
    transition: border-color 0.2s, background 0.2s;
}
.hero-basic-drop:hover, .hero-basic-drop.dragover { border-color: var(--color-primary); background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.05); }
.hero-basic-drop__text { display: block; font-weight: 500; color: var(--color-gray-700); margin-bottom: 0.25rem; }
.hero-basic-drop__hint { font-size: 0.8125rem; color: var(--color-gray-500); }
.hero-basic-drop--hidden { display: none !important; }
.hero-crop-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.hero-crop-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.6); }
.hero-crop-modal__box { position: relative; background: #fff; border-radius: 12px; max-width: 900px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.hero-crop-modal__header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-gray-200); flex-shrink: 0; }
.hero-crop-modal__title { margin: 0 2rem 0 0; font-size: 1.125rem; }
.hero-crop-modal__subtitle { margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--color-gray-600); }
.hero-crop-modal__close { position: absolute; top: 1rem; right: 1rem; width: 32px; height: 32px; border: none; background: none; font-size: 1.5rem; line-height: 1; color: var(--color-gray-500); cursor: pointer; }
.hero-crop-modal__close:hover { color: var(--color-gray-800); }
.hero-crop-modal__body { padding: 1rem; overflow: hidden; flex: 1; min-height: 0; }
.hero-crop-container { width: 100%; height: 60vh; max-height: 500px; background: #000; overflow: hidden; }
.hero-crop-container img { max-width: 100%; max-height: 100%; display: block; }
.hero-crop-modal__footer { padding: 1rem 1.25rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; flex-shrink: 0; }
</style>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous" />
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>

<!-- JavaScript for Tab Navigation -->
<script>
    function getActiveMainTab() {
        var b = document.querySelector('.tab-button.active');
        return b && b.getAttribute('data-tab') ? b.getAttribute('data-tab') : 'basic';
    }

    function getActiveContentSub() {
        var b = document.querySelector('.content-subtab-btn.active');
        return b && b.getAttribute('data-content-sub') ? b.getAttribute('data-content-sub') : 'overview';
    }

    function syncProgramEditUrl() {
        var t = getActiveMainTab();
        if (!t || t === 'activities') { return; }
        var u = new URL(window.location.href);
        u.search = '';
        u.searchParams.set('tab', t);
        if (t === 'content') {
            u.searchParams.set('sub', getActiveContentSub());
        }
        var newUrl = u.pathname + u.search;
        if (newUrl === window.location.pathname + window.location.search) { return; }
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', newUrl);
        }
    }

    function switchContentSubTab(sub, opts) {
        opts = opts || {};
        document.querySelectorAll('.content-subpanel').forEach(function (el) {
            el.classList.remove('active');
        });
        document.querySelectorAll('.content-subtab-btn').forEach(function (btn) {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        });
        var panel = document.getElementById('content-sub-' + sub);
        var btn = document.querySelector('.content-subtab-btn[data-content-sub="' + sub + '"]');
        if (panel) panel.classList.add('active');
        if (btn) {
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
        }
        try {
            sessionStorage.setItem('programEditContentSub', sub);
        } catch (e) { /* ignore */ }
        if (!opts.skipUrl && getActiveMainTab() === 'content') {
            syncProgramEditUrl();
        }
    }

    function switchTab(tabName, opts) {
        opts = opts || {};
        if (!document.getElementById(tabName + '-tab')) { return; }
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(function (tab) {
            tab.classList.remove('active');
        });

        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(function (button) {
            button.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.add('active');

        // Add active class to clicked button
        var mainBtn = document.querySelector('.tab-button[data-tab="' + tabName + '"]');
        if (mainBtn) { mainBtn.classList.add('active'); }

        if (tabName === 'content') {
            var sub = 'overview';
            try {
                var saved = sessionStorage.getItem('programEditContentSub');
                if (saved && document.getElementById('content-sub-' + saved)) {
                    sub = saved;
                }
            } catch (e) { /* ignore */ }
            switchContentSubTab(sub, { skipUrl: opts.skipUrl });
        } else if (!opts.skipUrl) {
            syncProgramEditUrl();
        }
    }

    function confirmDelete(url) {
        swalConfirm({ title: 'คุณแน่ใจว่าต้องการลบไฟล์นี้?', confirmText: 'ลบ', cancelText: 'ยกเลิก' }).then(function(ok) {
            if (ok) window.location.href = url;
        });
    }

    function loadProgramNews() {
        var listEl = document.getElementById('program-news-list');
        if (!listEl) return;
        var programId = <?= (int)($program['id'] ?? 0) ?>;
        var baseUrl = '<?= base_url() ?>';
        fetch(baseUrl + 'program-admin/news/' + programId)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success || !res.data) { listEl.innerHTML = '<p style="color: var(--color-gray-500);">ไม่มีข่าว</p>'; return; }
                var data = res.data;
                if (data.length === 0) {
                    listEl.innerHTML = '<p style="color: var(--color-gray-500);">ยังไม่มีข่าวที่แท็กกับหลักสูตรนี้</p>';
                    return;
                }
                var html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
                data.forEach(function (n) {
                    var thumb = n.thumb_url ? '<img src="' + n.thumb_url + '" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">' : '<span style="width: 60px; height: 40px; background: var(--color-gray-200); border-radius: 4px; display: inline-block;"></span>';
                    html += '<div style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px;">' + thumb + '<div style="flex: 1;"><a href="' + (n.url || (baseUrl + 'news/' + n.id)) + '" target="_blank" style="font-weight: 500;">' + (n.title || '') + '</a><div style="font-size: 0.875rem; color: var(--color-gray-600);">' + (n.created_at_formatted || '') + '</div></div><a href="' + baseUrl + 'admin/news/edit/' + n.id + '" class="btn btn-outline btn-sm" target="_blank">แก้ไข</a></div>';
                });
                html += '</div>';
                listEl.innerHTML = html;
            })
            .catch(function () { listEl.innerHTML = '<p style="color: var(--color-gray-500);">โหลดข่าวไม่สำเร็จ</p>'; });
    }

    var newsContentEditor = null;
    function initNewsCKEditor() {
        var ta = document.getElementById('news_content');
        if (!ta || newsContentEditor !== null) return;
        if (typeof ClassicEditor === 'undefined') {
            var s = document.createElement('script');
            s.src = 'https://cdn.ckeditor.com/ckeditor5/43.0.0/classic/ckeditor.js';
            s.onload = function() { startNewsCKEditor(); };
            document.head.appendChild(s);
        } else {
            startNewsCKEditor();
        }
    }
    function startNewsCKEditor() {
        var ta = document.getElementById('news_content');
        if (!ta || newsContentEditor !== null) return;
        ClassicEditor.create(ta, {
            language: 'th',
            placeholder: 'ใส่รายละเอียดข่าวที่นี่...',
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo']
        }).then(function(editor) {
            newsContentEditor = editor;
        }).catch(function(err) { console.warn('CKEditor init:', err); });
    }
    function ensureNewsEditorSync() {
        if (newsContentEditor && typeof newsContentEditor.getData === 'function') {
            var ta = document.getElementById('news_content');
            if (ta) ta.value = newsContentEditor.getData();
        }
    }

    // Theme color picker <-> hex sync & hero image preview
    (function() {
        var colorInput = document.getElementById('theme_color');
        var hexInput = document.getElementById('theme_color_hex');
        if (colorInput && hexInput) {
            colorInput.addEventListener('input', function() { hexInput.value = this.value; });
            hexInput.addEventListener('input', function() {
                var v = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(v)) colorInput.value = v;
            });
        }
        var heroFile = document.getElementById('hero_image');
        var heroPreview = document.getElementById('hero-preview-img');
        var heroPreviewWrap = heroPreview && heroPreview.closest('.hero-preview-wrap');
        var heroRemove = document.getElementById('hero_image_remove');
        if (heroFile && heroPreviewWrap) {
            heroFile.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    var r = new FileReader();
                    r.onload = function() { heroPreview.src = r.result; heroPreviewWrap.style.display = 'block'; };
                    r.readAsDataURL(this.files[0]);
                }
            });
        }
        if (heroRemove && heroPreviewWrap) {
            heroRemove.addEventListener('change', function() {
                heroPreviewWrap.style.display = this.checked ? 'none' : 'block';
            });
        }
    })();

    // Hero image: drag-drop + crop modal (แท็บข้อมูลพื้นฐาน)
    (function() {
        var programId = <?= (int)($program['id'] ?? 0) ?>;
        var uploadHeroUrl = '<?= base_url('program-admin/upload-hero/' . (int)($program['id'] ?? 0)) ?>';
        var csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
        var dropZone = document.getElementById('hero-basic-drop');
        var preview = document.getElementById('hero-basic-preview');
        var previewImg = document.getElementById('hero-basic-img');
        var fileInput = document.getElementById('hero-basic-file');
        var removeBtn = document.getElementById('hero-basic-remove');
        var modal = document.getElementById('hero-crop-modal');
        var cropImage = document.getElementById('hero-crop-image');
        var cropClose = document.getElementById('hero-crop-close');
        var cropCancel = document.getElementById('hero-crop-cancel');
        var cropConfirm = document.getElementById('hero-crop-confirm');
        var cropConfirmText = cropConfirm && cropConfirm.querySelector('.hero-crop-confirm-text');
        var cropConfirmLoading = cropConfirm && cropConfirm.querySelector('.hero-crop-confirm-loading');
        var cropperInstance = null;
        var currentObjectUrl = null;

        function openCropModal(file) {
            if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) return;
            if (currentObjectUrl) URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = URL.createObjectURL(file);
            cropImage.src = currentObjectUrl;
            modal.style.display = 'flex';
            if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
            setTimeout(function() {
                if (typeof Cropper !== 'undefined' && cropImage) {
                    cropperInstance = new Cropper(cropImage, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true
                    });
                }
            }, 100);
        }

        function closeCropModal() {
            modal.style.display = 'none';
            if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
            if (currentObjectUrl) { URL.revokeObjectURL(currentObjectUrl); currentObjectUrl = null; }
            if (fileInput) fileInput.value = '';
        }

        function uploadCropped() {
            if (!cropperInstance || !uploadHeroUrl) return;
            if (cropConfirmText) cropConfirmText.style.display = 'none';
            if (cropConfirmLoading) cropConfirmLoading.style.display = 'inline';
            cropConfirm.disabled = true;
            cropperInstance.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1080, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
                var fd = new FormData();
                fd.append('hero_image', blob, 'hero.jpg');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
                fetch(uploadHeroUrl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success && res.hero_url) {
                            previewImg.src = res.hero_url;
                            preview.style.display = 'block';
                            if (dropZone) dropZone.classList.add('hero-basic-drop--hidden');
                            closeCropModal();
                        } else {
                            alert(res.message || 'อัปโหลดไม่สำเร็จ');
                        }
                    })
                    .catch(function() { alert('เกิดข้อผิดพลาดในการเชื่อมต่อ'); })
                    .finally(function() {
                        cropConfirm.disabled = false;
                        if (cropConfirmText) cropConfirmText.style.display = 'inline';
                        if (cropConfirmLoading) cropConfirmLoading.style.display = 'none';
                    });
            }, 'image/jpeg', 0.9);
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) openCropModal(this.files[0]);
            });
        }
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); this.classList.add('dragover'); });
            dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); this.classList.remove('dragover'); });
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) openCropModal(e.dataTransfer.files[0]);
            });
        }
        if (cropClose) cropClose.addEventListener('click', closeCropModal);
        if (cropCancel) cropCancel.addEventListener('click', closeCropModal);
        if (cropConfirm) cropConfirm.addEventListener('click', uploadCropped);
        if (modal && modal.querySelector('.hero-crop-modal__backdrop')) {
            modal.querySelector('.hero-crop-modal__backdrop').addEventListener('click', closeCropModal);
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (!confirm('ต้องการลบรูปหน้าปกใช่หรือไม่?')) return;
                var fd = new FormData();
                fd.append('hero_image_remove', '1');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
                fetch(uploadHeroUrl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            previewImg.src = '';
                            preview.style.display = 'none';
                            if (dropZone) dropZone.classList.remove('hero-basic-drop--hidden');
                        }
                    });
            });
        }
    })();

    // Website color pickers sync
    (function() {
        var textColor = document.getElementById('text_color');
        var textColorHex = document.getElementById('text_color_hex');
        if (textColor && textColorHex) {
            textColor.addEventListener('input', function() { textColorHex.value = this.value; });
            textColorHex.addEventListener('input', function() {
                var v = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(v)) textColor.value = v;
            });
        }
        var bgColor = document.getElementById('background_color');
        var bgColorHex = document.getElementById('background_color_hex');
        if (bgColor && bgColorHex) {
            bgColor.addEventListener('input', function() { bgColorHex.value = this.value; });
            bgColorHex.addEventListener('input', function() {
                var v = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(v)) bgColor.value = v;
            });
        }
    })();

    // Initialize first tab (or tab from query string ?tab= & sub= สำหรับเนื้อหา)
    document.addEventListener('DOMContentLoaded', function() {
        var params = new URLSearchParams(window.location.search);
        var tab = params.get('tab');
        var sub = params.get('sub');
        var validTabs = ['basic', 'content', 'alumni', 'downloads', 'news', 'personnel', 'website'];
        var contentSubs = ['overview', 'quality', 'curriculum', 'pages', 'publish'];
        if (tab === 'content' && sub && contentSubs.indexOf(sub) >= 0) {
            try { sessionStorage.setItem('programEditContentSub', sub); } catch (e) { /* ignore */ }
        }
        if (tab && validTabs.indexOf(tab) >= 0 && document.getElementById(tab + '-tab')) {
            switchTab(tab, { skipUrl: false });
        } else {
            switchTab('basic', { skipUrl: !params.get('tab') });
        }
        document.getElementById('program-news-form') && document.getElementById('program-news-form').addEventListener('submit', function() {
            ensureNewsEditorSync();
        });
        document.querySelectorAll('.link-to-downloads-tab').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof switchTab === 'function') { switchTab('downloads'); }
            });
        });
    });
    var origSwitchTab = window.switchTab;
    if (typeof origSwitchTab === 'function') {
        window.switchTab = function(tabName, opts) {
            origSwitchTab(tabName, opts);
            if (tabName === 'news') { loadProgramNews(); setTimeout(initNewsCKEditor, 100); }
        };
    }

    // --- Ajax save: Basic Info ---
    (function() {
        var form = document.getElementById('basic-info-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('basic-save-btn');
            var msg = document.getElementById('basic-ajax-msg');
            if (!btn || !msg) return;
            btn.disabled = true;
            msg.textContent = 'กำลังบันทึก...';
            msg.style.color = 'var(--color-gray-600)';
            var fd = new FormData(form);
            fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    msg.textContent = res.message || (res.success ? 'บันทึกเรียบร้อย' : 'เกิดข้อผิดพลาด');
                    msg.style.color = res.success ? 'var(--secondary)' : 'var(--color-error)';
                })
                .catch(function() {
                    btn.disabled = false;
                    msg.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                    msg.style.color = 'var(--color-error)';
                });
        });
    })();

    // --- Ajax save: Content Page ---
    (function() {
        var form = document.getElementById('content-page-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('content-save-btn');
            var msg = document.getElementById('content-ajax-msg');
            if (!btn || !msg) return;
            btn.disabled = true;
            msg.textContent = 'กำลังบันทึก...';
            msg.style.color = 'var(--color-gray-600)';
            if (typeof window.buildElosJson === 'function') window.buildElosJson();
            if (typeof window.buildCurriculumJson === 'function') window.buildCurriculumJson();
            var fd = new FormData(form);
            fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    msg.textContent = res.message || (res.success ? 'บันทึกเรียบร้อย' : 'เกิดข้อผิดพลาด');
                    msg.style.color = res.success ? 'var(--secondary)' : 'var(--color-error)';
                })
                .catch(function() {
                    btn.disabled = false;
                    msg.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                    msg.style.color = 'var(--color-error)';
                });
        });
    })();
</script>

<style>
    .tab-navigation {
        display: flex;
        border-bottom: 1px solid var(--color-gray-200);
        background: var(--color-gray-50);
    }

    .tab-button {
        padding: 1rem 1.5rem;
        border: none;
        background: none;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--color-gray-600);
    }

    .tab-button:hover {
        color: var(--color-gray-900);
        background: var(--color-gray-100);
    }

    .tab-button.active {
        color: var(--secondary);
        border-bottom-color: var(--secondary);
        background: white;
    }

    .content-subtab-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        padding: 0.65rem 1rem;
        border-bottom: 1px solid var(--color-gray-200);
        background: var(--color-gray-100);
    }
    .content-subtab-btn {
        border: 1px solid transparent;
        background: transparent;
        padding: 0.45rem 0.8rem;
        border-radius: 6px;
        font-size: 0.8125rem;
        color: var(--color-gray-700);
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }
    .content-subtab-btn:hover { background: var(--color-gray-200); }
    .content-subtab-btn.active {
        background: #fff;
        border-color: var(--color-gray-300);
        font-weight: 600;
        color: var(--secondary);
    }
    .content-subpanel { display: none; }
    .content-subpanel.active { display: block; }

    .admin-upload-hint { line-height: 1.45; }
    .admin-upload-panel { box-sizing: border-box; }

    .program-section-matrix-wrap summary { list-style: none; }
    .program-section-matrix-wrap summary::-webkit-details-marker { display: none; }
    .program-section-matrix-wrap[open] > summary { border-bottom: 1px solid var(--color-gray-200); margin-bottom: 0.25rem; }

    .tab-content-container {
        background: white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block !important;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--color-gray-900);
    }

    .form-row {
        display: grid;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    @media (min-width: 768px) {
        .form-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--color-gray-700);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--color-gray-300);
        border-radius: 6px;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
    }

    .form-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-gray-200);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid transparent;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
        color: var(--color-dark);
        border-color: var(--color-primary-dark);
    }

    .btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.35);
    }

    .btn-secondary {
        background: var(--color-gray-200);
        color: var(--color-gray-700);
    }

    .btn-secondary:hover {
        background: var(--color-gray-300);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--color-gray-300);
        color: var(--color-gray-700);
    }

    .btn-outline:hover {
        background: var(--color-gray-50);
    }

    .btn-danger {
        background: var(--color-red-600);
        color: white;
    }

    .btn-danger:hover {
        background: var(--color-red-700);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .personnel-grid {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .personnel-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    .elos-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .elo-row {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 1rem;
        border: 1px solid var(--color-gray-200);
        border-radius: 8px;
        background: var(--color-gray-50);
    }

    .elo-row__fields {
        flex: 1;
        min-width: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .elo-row__actions {
        flex-shrink: 0;
    }

    .form-row--2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .elo-row { flex-direction: column; }
        .elo-row__fields { grid-template-columns: 1fr; }
        .form-row--2 { grid-template-columns: 1fr; }
    }

    .alumni-list { display: flex; flex-direction: column; gap: 1rem; }
    .alumni-row { display: flex; gap: 1rem; align-items: flex-start; padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px; background: var(--color-gray-50); flex-wrap: wrap; }
    .alumni-row__fields { flex: 1; min-width: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .alumni-row__fields .form-group { margin-bottom: 0; }
    .alumni-row__fields .form-group.full-width { grid-column: 1 / -1; }
    .alumni-row__photo { flex-shrink: 0; width: 100px; text-align: center; }
    .alumni-row__photo img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid var(--color-gray-200); }
    .alumni-row__photo .btn { margin-top: 0.35rem; font-size: 0.75rem; }
    .alumni-row__actions { flex-shrink: 0; }
    @media (max-width: 640px) { .alumni-row__fields { grid-template-columns: 1fr; } .alumni-row { flex-direction: column; } }

    .curriculum-list { display: flex; flex-direction: column; gap: 1.5rem; }
    .curriculum-year-card { border: 1px solid var(--color-gray-200); border-radius: 8px; padding: 1rem; background: var(--color-gray-50); }
    .curriculum-year-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
    .curriculum-year-body { margin-top: 1rem; margin-bottom: 0.5rem; }
    .curriculum-semester { margin-bottom: 1rem; padding: 0.75rem; background: white; border-radius: 6px; border: 1px solid var(--color-gray-200); }
    .curriculum-semester__head { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
    .curriculum-semester-name { flex: 1; min-width: 10rem; }
    .curriculum-course-table { width: 100%; border-collapse: collapse; margin-bottom: 0.5rem; font-size: 0.875rem; }
    .curriculum-course-table th, .curriculum-course-table td { padding: 0.35rem 0.5rem; text-align: left; }
    .curriculum-course-table th { font-weight: 600; color: var(--color-gray-700); }
    .structure-toolbar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem; }
    .ajax-msg { font-size: 0.875rem; font-weight: 500; margin-left: 0.5rem; transition: color 0.2s; }
</style>

<script>
(function () {
    function formatJson(taId) {
        var ta = document.getElementById(taId);
        if (!ta) return;
        var raw = (ta.value || '').trim();
        if (!raw) return;
        try {
            var obj = JSON.parse(raw);
            ta.value = JSON.stringify(obj, null, 2);
        } catch (e) {
            swalAlert('JSON ไม่ถูกต้อง: ' + e.message, 'error');
        }
    }
    // --- เนื้อหาหลักสูตร: มาตรฐานการเรียนรู้ + PLO/ELO ---
    var contentForm = document.querySelector('#content-tab form');
    if (!contentForm) return;

    var elosList = document.getElementById('elos-list');
    var elosJsonField = document.getElementById('elos_json');
    var lsIntroField = document.getElementById('learning-standards-intro');
    var lsList = document.getElementById('learning-standards-list');
    var lsMapList = document.getElementById('learning-standards-mapping-list');
    var lsJsonField = document.getElementById('learning_standards_json');

    function escapeHtml(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function addStandardRow(data) {
        data = data || {};
        var code = escapeHtml(data.code || '');
        var category = escapeHtml(data.category || '');
        var detail = escapeHtml(data.detail || '');
        var row = document.createElement('div');
        row.className = 'ls-row elo-row';
        row.innerHTML =
            '<div class="elo-row__fields">' +
            '<div class="form-group"><label class="form-label">รหัส (code)</label><input type="text" class="form-control ls-field ls-code" value="' + code + '" placeholder="เช่น LS1"></div>' +
            '<div class="form-group"><label class="form-label">หมวด / ด้าน</label><input type="text" class="form-control ls-field ls-category" value="' + category + '" placeholder="เช่น ด้านคุณธรรมจริยธรรม"></div>' +
            '<div class="form-group"><label class="form-label">รายละเอียด</label><textarea class="form-control ls-field ls-detail" rows="3" placeholder="ข้อความมาตรฐานการเรียนรู้">' + detail + '</textarea></div>' +
            '</div>' +
            '<div class="elo-row__actions"><button type="button" class="btn btn-danger btn-sm ls-remove-btn">ลบ</button></div>';
        lsList.appendChild(row);
        row.querySelector('.ls-remove-btn').addEventListener('click', function () { row.remove(); });
    }

    function addMappingRow(data) {
        data = data || {};
        var sc = escapeHtml(data.standard_code || '');
        var pr = escapeHtml(data.plo_refs || '');
        var row = document.createElement('div');
        row.className = 'ls-map-row elo-row';
        row.innerHTML =
            '<div class="elo-row__fields" style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:flex-end;">' +
            '<div class="form-group" style="flex:1; min-width:10rem;"><label class="form-label">รหัสมาตรฐาน</label><input type="text" class="form-control ls-map-field ls-map-code" value="' + sc + '" placeholder="LS1"></div>' +
            '<div class="form-group" style="flex:2; min-width:12rem;"><label class="form-label">PLO ที่เกี่ยวข้อง</label><input type="text" class="form-control ls-map-field ls-map-plo" value="' + pr + '" placeholder="PLO1, PLO2"></div>' +
            '</div>' +
            '<div class="elo-row__actions"><button type="button" class="btn btn-danger btn-sm ls-map-remove-btn">ลบ</button></div>';
        lsMapList.appendChild(row);
        row.querySelector('.ls-map-remove-btn').addEventListener('click', function () { row.remove(); });
    }

    function buildLearningStandardsJson() {
        if (!lsJsonField || !lsList) return '';
        var intro = (lsIntroField && lsIntroField.value) ? lsIntroField.value.trim() : '';
        var standards = [];
        lsList.querySelectorAll('.ls-row').forEach(function (row) {
            var code = (row.querySelector('.ls-code') && row.querySelector('.ls-code').value) || '';
            var category = (row.querySelector('.ls-category') && row.querySelector('.ls-category').value) || '';
            var detail = (row.querySelector('.ls-detail') && row.querySelector('.ls-detail').value) || '';
            var summary = detail.length > 120 ? detail.substring(0, 120) + '…' : detail;
            standards.push({
                code: code.trim(),
                category: category.trim(),
                title: category.trim() || code.trim() || ('มาตรฐาน ' + (standards.length + 1)),
                summary: summary,
                detail: detail
            });
        });
        var mapping = [];
        if (lsMapList) {
            lsMapList.querySelectorAll('.ls-map-row').forEach(function (row) {
                var sc = (row.querySelector('.ls-map-code') && row.querySelector('.ls-map-code').value) || '';
                var pr = (row.querySelector('.ls-map-plo') && row.querySelector('.ls-map-plo').value) || '';
                if (sc.trim() || pr.trim()) {
                    mapping.push({ standard_code: sc.trim(), plo_refs: pr.trim() });
                }
            });
        }
        var obj = { intro: intro, standards: standards, mapping: mapping };
        lsJsonField.value = JSON.stringify(obj);
        return lsJsonField.value;
    }

    window.buildLearningStandardsJson = buildLearningStandardsJson;

    var programId = <?= (int)($program['id'] ?? 0) ?>;
    var updatePageJsonUrl = '<?= base_url('program-admin/update-page-json/' . (int)($program['id'] ?? 0)) ?>';
    var csrfInput = contentForm.querySelector('input[name="csrf_test_name"]') || contentForm.querySelector('input[type="hidden"][name*="csrf"]');

    function showLsMsg(msg, isError) {
        var el = document.getElementById('ls-ajax-msg');
        if (el) { el.textContent = msg; el.style.color = isError ? 'var(--color-error)' : 'var(--secondary)'; }
    }

    if (lsList && lsJsonField) {
        document.getElementById('ls-save-ajax-btn') && document.getElementById('ls-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var json = buildLearningStandardsJson();
            btn.disabled = true;
            showLsMsg('กำลังบันทึก...');
            var fd = new FormData();
            fd.append('learning_standards_json', json);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    showLsMsg(res.success ? 'บันทึกมาตรฐานการเรียนรู้เรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'), !res.success);
                })
                .catch(function () { btn.disabled = false; showLsMsg('เกิดข้อผิดพลาดในการเชื่อมต่อ', true); });
        });

        var lsInitial = [];
        try {
            var lraw = lsList.getAttribute('data-initial') || '[]';
            lsInitial = JSON.parse(lraw);
            if (!Array.isArray(lsInitial)) lsInitial = [];
        } catch (e1) { lsInitial = []; }
        if (lsInitial.length === 0) {
            addStandardRow({});
        } else {
            lsInitial.forEach(function (item) { addStandardRow(item); });
        }

        var mapInitial = [];
        if (lsMapList) {
            try {
                var mraw = lsMapList.getAttribute('data-initial') || '[]';
                mapInitial = JSON.parse(mraw);
                if (!Array.isArray(mapInitial)) mapInitial = [];
            } catch (e2) { mapInitial = []; }
            if (mapInitial.length === 0) {
                addMappingRow({});
            } else {
                mapInitial.forEach(function (item) { addMappingRow(item); });
            }
        }

        document.getElementById('ls-add-btn') && document.getElementById('ls-add-btn').addEventListener('click', function () { addStandardRow({}); });
        document.getElementById('ls-add-mapping-btn') && document.getElementById('ls-add-mapping-btn').addEventListener('click', function () { addMappingRow({}); });
    }

    function addElosRow(data) {
        data = data || {};
        var category = escapeHtml(data.category || '');
        var detail = escapeHtml(data.detail || '');
        var row = document.createElement('div');
        row.className = 'elo-row';
        row.innerHTML =
            '<div class="elo-row__fields">' +
            '<div class="form-group"><label class="form-label">หมวด (category)</label><input type="text" class="form-control elo-field elo-category" value="' + category + '" placeholder="เช่น PLO1 / ความรู้"></div>' +
            '<div class="form-group"><label class="form-label">รายละเอียด (detail)</label><textarea class="form-control elo-field elo-detail" rows="3" placeholder="อธิบายผลลัพธ์การเรียนรู้ที่คาดหวัง">' + detail + '</textarea></div>' +
            '</div>' +
            '<div class="elo-row__actions"><button type="button" class="btn btn-danger btn-sm elo-remove-btn">ลบ</button></div>';
        elosList.appendChild(row);
        row.querySelector('.elo-remove-btn').addEventListener('click', function () { row.remove(); });
    }

    function buildElosJson() {
        if (!elosList || !elosJsonField) return '';
        var rows = elosList.querySelectorAll(':scope > .elo-row');
        var arr = [];
        rows.forEach(function (row) {
            var category = (row.querySelector('.elo-category') && row.querySelector('.elo-category').value) || '';
            var detail = (row.querySelector('.elo-detail') && row.querySelector('.elo-detail').value) || '';
            var summary = detail.length > 120 ? detail.substring(0, 120) + '…' : detail;
            arr.push({ category: category, title: category || ('ELO ' + (arr.length + 1)), summary: summary, detail: detail });
        });
        elosJsonField.value = JSON.stringify(arr);
        return elosJsonField.value;
    }

    window.buildElosJson = buildElosJson;

    function showElosMsg(msg, isError) {
        var el = document.getElementById('elos-ajax-msg');
        if (el) { el.textContent = msg; el.style.color = isError ? 'var(--color-error)' : 'var(--secondary)'; }
    }

    if (elosList && elosJsonField) {
        document.getElementById('elos-save-ajax-btn') && document.getElementById('elos-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var json = buildElosJson();
            btn.disabled = true;
            showElosMsg('กำลังบันทึก...');
            var fd = new FormData();
            fd.append('elos_json', json);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    showElosMsg(res.success ? 'บันทึก PLO/ELO เรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'), !res.success);
                })
                .catch(function () { btn.disabled = false; showElosMsg('เกิดข้อผิดพลาดในการเชื่อมต่อ', true); });
        });

        var initialData = [];
        try {
            var raw = elosList.getAttribute('data-initial') || '[]';
            initialData = JSON.parse(raw);
            if (!Array.isArray(initialData)) initialData = [];
        } catch (e) {
            initialData = [];
        }
        if (initialData.length === 0) {
            addElosRow({});
        } else {
            initialData.forEach(function (item) {
                addElosRow(item);
            });
        }

        document.getElementById('elos-add-btn') && document.getElementById('elos-add-btn').addEventListener('click', function () {
            addElosRow({});
        });
    }

    // --- โครงสร้างหลักสูตร / แผนการเรียน: หัวข้อ + รายละเอียด (บันทึกเป็น HTML มาร์กเกอร์ ไม่ใช่ JSON) ---
    (function initPtbEditors() {
        function bodyLooksLikeHtml(s) {
            if (!s) return false;
            return /<(img|a|p|ul|ol|h\d|br|div|span|table|tr|td|th|em|strong|b|i|hr)\b/i.test(s);
        }
        function bodyToStoredHtml(s) {
            if (!s) return '';
            if (bodyLooksLikeHtml(s)) {
                return s.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '');
            }
            return escapeHtml(s).replace(/\n/g, '<br>');
        }
        function parsePtb(raw, kind) {
            if (!raw || !String(raw).trim()) return [{ title: '', body: '' }];
            var s = String(raw);
            if (s.indexOf('ptb-' + kind) === -1) {
                return [{ title: '', body: s.trim() }];
            }
            var doc;
            try {
                doc = new DOMParser().parseFromString(s, 'text/html');
            } catch (e) {
                return [{ title: '', body: s.trim() }];
            }
            var root = doc.querySelector('.ptb-' + kind);
            if (!root) return [{ title: '', body: s.trim() }];
            var blocks = root.querySelectorAll('.ptb-block');
            if (!blocks || !blocks.length) return [{ title: '', body: s.trim() }];
            var out = [];
            for (var i = 0; i < blocks.length; i++) {
                var b = blocks[i];
                var th = b.querySelector('.ptb-title');
                var bd = b.querySelector('.ptb-body');
                out.push({
                    title: th ? th.textContent.trim() : '',
                    body: bd ? (bd.innerHTML || '').trim() : ''
                });
            }
            return out.length ? out : [{ title: '', body: s.trim() }];
        }
        function serializePtb(rows, kind) {
            var cls = 'ptb ptb-' + kind;
            var h = '\n<!--ptb:' + kind + ':v1-->\n<div class="' + cls + '" data-ptb-version="1">';
            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                var title = r.title != null ? String(r.title) : '';
                var body = r.body != null ? String(r.body) : '';
                if (!title.trim() && !body.trim()) continue;
                h += '<section class="ptb-block"><h3 class="ptb-title">' + escapeHtml(title) + '</h3><div class="ptb-body">' + bodyToStoredHtml(body) + '</div></section>';
            }
            h += '</div>\n<!--/ptb:' + kind + ':v1-->\n';
            return h.trim() ? h : '';
        }
        function addPtbRow(wrap, fieldName, data) {
            data = data || {};
            var row = document.createElement('div');
            row.className = 'ptb-dyn-block';
            row.style.cssText = 'border:1px solid var(--color-gray-200); border-radius:8px; padding:0.9rem; margin-bottom:0.6rem; background:#fafafa;';
            row.innerHTML =
                '<div class="form-group" style="margin:0 0 0.6rem 0;">' +
                '<label class="form-label" style="font-size:0.8rem;">หัวข้อ</label>' +
                '<input type="text" class="form-control ptb-block-title" maxlength="500" value="" placeholder="เช่น โครงสร้างเครดิตรวม">' +
                '</div>' +
                '<div class="form-group" style="margin:0;">' +
                '<label class="form-label" style="font-size:0.8rem;">รายละเอียด</label>' +
                '<textarea class="form-control ptb-block-body" rows="5" data-ptb-field="' + fieldName + '"></textarea>' +
                '</div>' +
                '<div style="margin-top:0.5rem;text-align:right;"><button type="button" class="btn btn-outline btn-sm ptb-row-remove">ลบก้อนนี้</button></div>';
            var titleIn = row.querySelector('.ptb-block-title');
            var bodyIn = row.querySelector('.ptb-block-body');
            if (titleIn) titleIn.value = data.title != null ? data.title : '';
            if (bodyIn) bodyIn.value = data.body != null ? data.body : '';
            if (bodyIn) {
                bodyIn.addEventListener('focus', function () { window._ptbLastBody = bodyIn; });
            }
            row.querySelector('.ptb-row-remove').addEventListener('click', function () { row.remove(); syncPtbField(fieldName); });
            ['input', 'change'].forEach(function (ev) {
                row.addEventListener(ev, function (e) {
                    if (e.target && (e.target.classList.contains('ptb-block-title') || e.target.classList.contains('ptb-block-body'))) {
                        syncPtbField(fieldName);
                    }
                });
            });
            wrap.appendChild(row);
        }
        function syncPtbField(fieldName) {
            var wrap = document.getElementById('ptb-wrap-' + fieldName);
            var hidden = document.getElementById(fieldName);
            if (!wrap || !hidden) return;
            var kind = wrap.getAttribute('data-ptb-kind') || (fieldName === 'study_plan' ? 'study' : 'curriculum');
            var rows = [];
            wrap.querySelectorAll('.ptb-dyn-block').forEach(function (row) {
                var t = (row.querySelector('.ptb-block-title') && row.querySelector('.ptb-block-title').value) || '';
                var b = (row.querySelector('.ptb-block-body') && row.querySelector('.ptb-block-body').value) || '';
                if (!t.trim() && !b.trim()) return;
                rows.push({ title: t, body: b });
            });
            hidden.value = rows.length ? serializePtb(rows, kind) : '';
        }
        function initWrap(fieldName) {
            var wrap = document.getElementById('ptb-wrap-' + fieldName);
            var hidden = document.getElementById(fieldName);
            if (!wrap || !hidden) return;
            var kind = wrap.getAttribute('data-ptb-kind') || (fieldName === 'study_plan' ? 'study' : 'curriculum');
            var initial = (hidden && hidden.value) ? hidden.value : '';
            var rows = parsePtb(initial, kind);
            wrap.innerHTML = '';
            if (rows.length === 0) rows = [{ title: '', body: '' }];
            for (var i = 0; i < rows.length; i++) {
                addPtbRow(wrap, fieldName, rows[i]);
            }
            var firstB = wrap.querySelector('.ptb-block-body');
            if (firstB) window._ptbLastBody = firstB;
            syncPtbField(fieldName);
        }
        window.syncPtbField = function () {};
        window.syncPtbAll = function () {};
        if (document.getElementById('ptb-wrap-curriculum_structure')) {
            initWrap('curriculum_structure');
            initWrap('study_plan');
            document.getElementById('ptb-add-curriculum_structure') && document.getElementById('ptb-add-curriculum_structure').addEventListener('click', function () {
                var w = document.getElementById('ptb-wrap-curriculum_structure');
                if (w) addPtbRow(w, 'curriculum_structure', {});
            });
            document.getElementById('ptb-add-study_plan') && document.getElementById('ptb-add-study_plan').addEventListener('click', function () {
                var w = document.getElementById('ptb-wrap-study_plan');
                if (w) addPtbRow(w, 'study_plan', {});
            });
            document.querySelectorAll('.ptb-insert-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var fid = this.getAttribute('data-ptb-field');
                    if (!fid) return;
                    var ins = this.getAttribute('data-insert') || '';
                    var body = window._ptbLastBody;
                    if (!body || body.getAttribute('data-ptb-field') !== fid) {
                        var w = document.getElementById('ptb-wrap-' + fid);
                        body = w ? w.querySelector('.ptb-block-body') : null;
                    }
                    if (body) {
                        var start = typeof body.selectionStart === 'number' ? body.selectionStart : body.value.length;
                        var end = typeof body.selectionEnd === 'number' ? body.selectionEnd : body.value.length;
                        var v = body.value;
                        body.value = v.substring(0, start) + ins + v.substring(end);
                        if (typeof body.setSelectionRange === 'function') {
                            var p = start + ins.length;
                            body.setSelectionRange(p, p);
                        }
                        body.focus();
                        syncPtbField(fid);
                    }
                });
            });
            document.addEventListener('focusin', function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('ptb-block-body')) {
                    window._ptbLastBody = e.target;
                }
            });
            window.syncPtbField = syncPtbField;
            window.syncPtbAll = function () {
                syncPtbField('curriculum_structure');
                syncPtbField('study_plan');
            };
        }
    })();

    (function initOverviewLineEditors() {
        function addRow(wrap, val) {
            var row = document.createElement('div');
            row.className = 'ol-line-row';
            row.style.cssText = 'display:flex; gap:0.5rem; align-items:center; margin-bottom:0.45rem;';
            row.innerHTML =
                '<span class="ol-line-badge" style="min-width:1.6rem; text-align:center; font-size:0.8rem; color:var(--color-gray-500); font-weight:600;">1</span>' +
                '<input type="text" class="form-control ol-line-input" maxlength="2000" value="" style="flex:1;" placeholder="พิมพ์ข้อความข้อนี้" />' +
                '<button type="button" class="btn btn-outline btn-sm ol-line-remove" title="ลบข้อนี้">ลบ</button>';
            var inp = row.querySelector('.ol-line-input');
            if (inp && val != null) inp.value = val;
            row.querySelector('.ol-line-remove').addEventListener('click', function () { row.remove(); renumber(wrap); syncOverviewOne(wrap); });
            ['input', 'change'].forEach(function (ev) { row.addEventListener(ev, function () { syncOverviewOne(wrap); }); });
            wrap.appendChild(row);
            renumber(wrap);
        }
        function renumber(wrap) {
            if (!wrap) return;
            var n = 1;
            wrap.querySelectorAll('.ol-line-row').forEach(function (r) {
                var b = r.querySelector('.ol-line-badge');
                if (b) b.textContent = String(n);
                n++;
            });
        }
        function syncOverviewOne(wrap) {
            if (!wrap) return;
            var hidden = wrap.id === 'objectives-line-editor' ? document.getElementById('objectives') : document.getElementById('graduate_profile');
            if (!hidden) return;
            var arr = [];
            wrap.querySelectorAll('.ol-line-input').forEach(function (inp) {
                var v = (inp.value || '').trim();
                if (v) arr.push(v);
            });
            hidden.value = JSON.stringify(arr);
        }
        function initEditor(editorId, addBtnId) {
            var wrap = document.getElementById(editorId);
            var addBtn = document.getElementById(addBtnId);
            if (!wrap) return;
            var initial = [];
            try { initial = JSON.parse(wrap.getAttribute('data-initial') || '[]'); } catch (e1) { initial = []; }
            if (!Array.isArray(initial)) initial = [];
            if (initial.length === 0) initial = [''];
            initial.forEach(function (line) { addRow(wrap, line == null ? '' : line); });
            if (addBtn) addBtn.addEventListener('click', function () { addRow(wrap, ''); });
            renumber(wrap);
            syncOverviewOne(wrap);
        }
        initEditor('objectives-line-editor', 'objectives-line-add');
        initEditor('graduate-line-editor', 'graduate-line-add');
        window.syncOverviewLineEditors = function () {
            var o = document.getElementById('objectives-line-editor');
            var g = document.getElementById('graduate-line-editor');
            if (o) syncOverviewOne(o);
            if (g) syncOverviewOne(g);
        };
    })();

    contentForm.addEventListener('submit', function () {
        if (typeof buildLearningStandardsJson === 'function') buildLearningStandardsJson();
        if (typeof buildElosJson === 'function') buildElosJson();
        if (typeof window.syncOverviewLineEditors === 'function') window.syncOverviewLineEditors();
        if (typeof window.syncPtbAll === 'function') window.syncPtbAll();
        if (typeof buildCareersJson === 'function') buildCareersJson();
        if (typeof buildTuitionJson === 'function') buildTuitionJson();
    });

    // --- ค่าเล่าเรียน: label + amount + note -> tuition_fees_json ---
    (function () {
        var editor = document.getElementById('tuition-fees-editor');
        var hidden = document.getElementById('tuition_fees_json');
        var addBtn = document.getElementById('tuition-fee-add-btn');
        if (!editor || !hidden) return;

        function addRow(data) {
            data = data || {};
            var row = document.createElement('div');
            row.className = 'tuition-fee-row';
            row.style.cssText = 'display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; padding: 0.9rem; margin-bottom: 0.5rem; border: 1px solid var(--color-gray-200); border-radius: 8px; background: #fafafa;';
            row.innerHTML =
                '<div class="form-group" style="flex: 1; min-width: 200px; margin:0"><label class="form-label" style="font-size:0.8rem">รายการ</label>' +
                '<input type="text" class="form-control tf-label" maxlength="200" value="" placeholder="เช่น ค่าลงทะเบียนเรียน (ภาคปกติ)"></div>' +
                '<div class="form-group" style="flex: 1; min-width: 180px; margin:0"><label class="form-label" style="font-size:0.8rem">จำนวนเงิน / ข้อความ</label>' +
                '<input type="text" class="form-control tf-amount" maxlength="500" value="" placeholder="เช่น 15,000 บาท / ภาค"></div>' +
                '<div class="form-group" style="flex: 1 1 100%; min-width: 0; margin:0"><label class="form-label" style="font-size:0.8rem">หมายเหตุ (ไม่บังคับ)</label>' +
                '<input type="text" class="form-control tf-note" maxlength="500" value="" placeholder="เช่น อ้างอิงประกาศ 2567"></div>' +
                '<button type="button" class="btn btn-outline btn-sm tuition-row-remove" style="align-self: flex-end;">ลบ</button>';
            var lb = row.querySelector('.tf-label');
            var am = row.querySelector('.tf-amount');
            var nt = row.querySelector('.tf-note');
            if (lb) lb.value = data.label || data.title || '';
            if (am) am.value = data.amount || data.value || '';
            if (nt) nt.value = data.note || data.remark || '';
            row.querySelector('.tuition-row-remove').addEventListener('click', function () { row.remove(); sync(); });
            ['input', 'change'].forEach(function (ev) {
                row.addEventListener(ev, function (e) {
                    if (e.target && (e.target.classList.contains('tf-label') || e.target.classList.contains('tf-amount') || e.target.classList.contains('tf-note'))) sync();
                });
            });
            editor.appendChild(row);
        }

        function sync() {
            var rows = editor.querySelectorAll('.tuition-fee-row');
            var arr = [];
            rows.forEach(function (row) {
                var label = (row.querySelector('.tf-label') || { value: '' }).value || '';
                var amount = (row.querySelector('.tf-amount') || { value: '' }).value || '';
                var note = (row.querySelector('.tf-note') || { value: '' }).value || '';
                if (!label.trim() && !amount.trim() && !note.trim()) return;
                arr.push({ label: label.trim(), amount: amount.trim(), note: note.trim() });
            });
            hidden.value = JSON.stringify(arr);
        }
        window.buildTuitionJson = sync;

        var initial = [];
        try { initial = JSON.parse((hidden && hidden.value) ? hidden.value : '[]'); } catch (e) { initial = []; }
        if (!Array.isArray(initial)) initial = [];
        if (initial.length === 0) {
            addRow({ label: '', amount: '', note: '' });
        } else {
            initial.forEach(function (it) { addRow(it); });
        }
        if (addBtn) addBtn.addEventListener('click', function () { addRow({}); sync(); });
        sync();
    })();

    // --- อาชีพ (การ์ด): title + คำอธิบาย + ไอคอน -> careers_json ---
    (function () {
        var editor = document.getElementById('career-cards-editor');
        var hidden = document.getElementById('careers_json');
        var addBtn = document.getElementById('career-card-add-btn');
        if (!editor || !hidden) return;

        var ICON_KEYS = <?= json_encode(career_icon_whitelist(), JSON_UNESCAPED_UNICODE) ?>;
        var ICON_LABELS = { cpu: 'เทคโนโลยี / คอมพิวเตอร์', chart: 'วิเคราะห์ / ข้อมูล', search: 'วิจัย / สำรวจ', code: 'พัฒนา / โปรแกรม', users: 'ทีม / บริการ', rocket: 'โอกาส / ก้าวหน้า', mortar: 'การศึกษา / ฝึกอบรม', target: 'เป้าหมาย / มุ่งมั่น', briefcase: 'องค์กร / ธุรกิจ', book: 'วิชาการ / ตำรา' };

        function selectOptions(val) {
            var h = '';
            ICON_KEYS.forEach(function (k) {
                h += '<option value="' + k + '"' + (k === val ? ' selected' : '') + '>' + (ICON_LABELS[k] || k) + '</option>';
            });
            return h;
        }

        function addRow(data) {
            data = data || {};
            var row = document.createElement('div');
            row.className = 'career-card-row';
            row.style.cssText = 'display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; padding: 0.9rem; margin-bottom: 0.5rem; border: 1px solid var(--color-gray-200); border-radius: 8px; background: #fafafa;';
            var icon = (data.icon && ICON_KEYS.indexOf(data.icon) >= 0) ? data.icon : 'rocket';
            row.innerHTML =
                '<div class="form-group" style="flex: 1; min-width: 180px; margin:0"><label class="form-label" style="font-size:0.8rem">ชื่ออาชีพ / บทบาท</label>' +
                '<input type="text" class="form-control cc-title" maxlength="200" value="" placeholder="เช่น นักวิเคราะห์ข้อมูล"></div>' +
                '<div class="form-group" style="min-width: 160px; max-width: 220px; margin:0"><label class="form-label" style="font-size:0.8rem">ไอคอน</label>' +
                '<select class="form-control cc-icon">' + selectOptions(icon) + '</select></div>' +
                '<div class="form-group" style="flex: 1 1 100%; min-width: 0; margin:0"><label class="form-label" style="font-size:0.8rem">คำอธิบายสั้น (แสดงใต้หัวข้อ)</label>' +
                '<textarea class="form-control cc-desc" rows="2" maxlength="2000" placeholder="สายงาน หรือลักษณะการประกอบอาชีพ"></textarea></div>' +
                '<button type="button" class="btn btn-outline btn-sm career-row-remove" style="align-self: flex-end;">ลบ</button>';
            var titleIn = row.querySelector('.cc-title');
            var descIn = row.querySelector('.cc-desc');
            if (titleIn) titleIn.value = data.title || '';
            if (descIn) descIn.value = (data.desc != null && data.desc !== '') ? data.desc : (data.description || '');
            row.querySelector('.career-row-remove').addEventListener('click', function () { row.remove(); sync(); });
            ['input', 'change'].forEach(function (ev) {
                row.addEventListener(ev, function (e) { if (e.target && (e.target.classList.contains('cc-title') || e.target.classList.contains('cc-desc') || e.target.classList.contains('cc-icon'))) sync(); });
            });
            editor.appendChild(row);
        }

        function sync() {
            var rows = editor.querySelectorAll('.career-card-row');
            var arr = [];
            rows.forEach(function (row) {
                var t = (row.querySelector('.cc-title') || { value: '' }).value || '';
                var d = (row.querySelector('.cc-desc') || { value: '' }).value || '';
                var ic = (row.querySelector('.cc-icon') || { value: 'rocket' }).value || 'rocket';
                if (!t.trim() && !d.trim()) return;
                arr.push({ title: t.trim(), desc: d.trim(), icon: ic });
            });
            hidden.value = JSON.stringify(arr);
        }
        window.buildCareersJson = sync;

        var initial = [];
        try { initial = JSON.parse((hidden && hidden.value) ? hidden.value : '[]'); } catch (e) { initial = []; }
        if (!Array.isArray(initial)) initial = [];
        if (initial.length === 0) {
            addRow({ title: '', desc: '', icon: 'rocket' });
        } else {
            initial.forEach(function (it) { addRow(it); });
        }
        if (addBtn) addBtn.addEventListener('click', function () { addRow({}); sync(); });
        sync();
    })();

    // --- ศิษย์เก่าถึงรุ่นน้อง: repeater + อัปโหลดรูป (ครอป 1:1) + บันทึก Ajax ---
    var alumniList = document.getElementById('alumni-list');
    var uploadAlumniPhotoUrl = '<?= base_url('program-admin/upload-alumni-photo/' . (int)($program['id'] ?? 0)) ?>';
    var serveBase = '<?= rtrim(base_url(), '/') ?>';
    if (alumniList) {
        var alumniCropModal = document.getElementById('alumni-crop-modal');
        var alumniCropImage = document.getElementById('alumni-crop-image');
        var alumniCropClose = document.getElementById('alumni-crop-close');
        var alumniCropCancel = document.getElementById('alumni-crop-cancel');
        var alumniCropConfirm = document.getElementById('alumni-crop-confirm');
        var alumniCropperInstance = null;
        var alumniCropObjectUrl = null;
        var currentAlumniRow = null;

        function openAlumniCropModal(file, row) {
            if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) return;
            if (alumniCropObjectUrl) URL.revokeObjectURL(alumniCropObjectUrl);
            alumniCropObjectUrl = URL.createObjectURL(file);
            currentAlumniRow = row;
            alumniCropImage.src = alumniCropObjectUrl;
            if (alumniCropModal) alumniCropModal.style.display = 'flex';
            if (alumniCropperInstance) { alumniCropperInstance.destroy(); alumniCropperInstance = null; }
            setTimeout(function () {
                if (typeof Cropper !== 'undefined' && alumniCropImage) {
                    alumniCropperInstance = new Cropper(alumniCropImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true
                    });
                }
            }, 100);
        }
        function closeAlumniCropModal() {
            if (alumniCropModal) alumniCropModal.style.display = 'none';
            if (alumniCropperInstance) { alumniCropperInstance.destroy(); alumniCropperInstance = null; }
            if (alumniCropObjectUrl) { URL.revokeObjectURL(alumniCropObjectUrl); alumniCropObjectUrl = null; }
            currentAlumniRow = null;
        }
        function uploadAlumniCropped() {
            if (!alumniCropperInstance || !uploadAlumniPhotoUrl || !currentAlumniRow) return;
            var confirmText = alumniCropConfirm && alumniCropConfirm.querySelector('.alumni-crop-confirm-text');
            var confirmLoading = alumniCropConfirm && alumniCropConfirm.querySelector('.alumni-crop-confirm-loading');
            if (confirmText) confirmText.style.display = 'none';
            if (confirmLoading) confirmLoading.style.display = 'inline';
            alumniCropConfirm.disabled = true;
            alumniCropperInstance.getCroppedCanvas({ width: 400, height: 400, imageSmoothingQuality: 'high' }).toBlob(function (blob) {
                var fd = new FormData();
                fd.append('photo', blob, 'alumni.jpg');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
                var row = currentAlumniRow;
                fetch(uploadAlumniPhotoUrl, { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res.success && res.path) {
                            var pathInput = row.querySelector('.alumni-photo-path');
                            var preview = row.querySelector('.alumni-photo-preview');
                            if (pathInput) pathInput.value = res.path;
                            if (preview) preview.src = res.photo_url || (serveBase + '/serve/uploads/' + res.path);
                            closeAlumniCropModal();
                        } else {
                            alert(res.message || 'อัปโหลดไม่สำเร็จ');
                        }
                    })
                    .catch(function () { alert('เกิดข้อผิดพลาดในการเชื่อมต่อ'); })
                    .finally(function () {
                        alumniCropConfirm.disabled = false;
                        if (confirmText) confirmText.style.display = 'inline';
                        if (confirmLoading) confirmLoading.style.display = 'none';
                    });
            }, 'image/jpeg', 0.9);
        }
        if (alumniCropClose) alumniCropClose.addEventListener('click', closeAlumniCropModal);
        if (alumniCropCancel) alumniCropCancel.addEventListener('click', closeAlumniCropModal);
        if (alumniCropConfirm) alumniCropConfirm.addEventListener('click', uploadAlumniCropped);
        if (alumniCropModal && alumniCropModal.querySelector('.hero-crop-modal__backdrop')) {
            alumniCropModal.querySelector('.hero-crop-modal__backdrop').addEventListener('click', closeAlumniCropModal);
        }

        // --- ภาพปกข่าวหลักสูตร: crop แล้วส่ง base64 (เหมือน admin/news) ---
        (function () {
            var pnModal = document.getElementById('program-news-featured-crop-modal');
            var pnCropImage = document.getElementById('program-news-featured-crop-image');
            var pnFileInput = document.getElementById('news_featured_image');
            var pnBox = document.getElementById('programNewsFeaturedBox');
            var pnPlaceholder = document.getElementById('programNewsFeaturedPlaceholder');
            var pnBase64Input = document.getElementById('program_news_featured_image_base64');
            var pnCloseBtn = document.getElementById('program-news-featured-crop-close');
            var pnCancelBtn = document.getElementById('program-news-featured-crop-cancel');
            var pnConfirmBtn = document.getElementById('program-news-featured-crop-confirm');
            var pnCropper = null;
            var pnObjectUrl = null;
            function pnOpenModal(file) {
                if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) return;
                if (pnObjectUrl) URL.revokeObjectURL(pnObjectUrl);
                pnObjectUrl = URL.createObjectURL(file);
                pnCropImage.src = pnObjectUrl;
                if (pnModal) pnModal.style.display = 'flex';
                if (pnCropper) { pnCropper.destroy(); pnCropper = null; }
                setTimeout(function () {
                    if (typeof Cropper !== 'undefined' && pnCropImage) {
                        pnCropper = new Cropper(pnCropImage, { aspectRatio: 16 / 9, viewMode: 1, dragMode: 'move', autoCropArea: 0.8, restore: false, guides: true, center: true, highlight: false, cropBoxMovable: true, cropBoxResizable: true });
                    }
                }, 100);
            }
            function pnCloseModal() {
                if (pnModal) pnModal.style.display = 'none';
                if (pnCropper) { pnCropper.destroy(); pnCropper = null; }
                if (pnObjectUrl) { URL.revokeObjectURL(pnObjectUrl); pnObjectUrl = null; }
                if (pnFileInput) pnFileInput.value = '';
            }
            function pnApplyCrop() {
                if (!pnCropper || !pnBase64Input) return;
                pnCropper.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1080, imageSmoothingQuality: 'high' }).toBlob(function (blob) {
                    var reader = new FileReader();
                    reader.onload = function () {
                        pnBase64Input.value = reader.result;
                        if (pnFileInput) pnFileInput.value = '';
                        if (pnPlaceholder) pnPlaceholder.innerHTML = '<div class="featured-image-preview"><img src="' + reader.result + '" alt="" style="max-width:100%;max-height:200px;object-fit:contain;"></div><p style="margin:0.5rem 0 0;font-size:0.875rem;color:var(--color-gray-500);">คลิกเพื่อเปลี่ยนภาพ</p>';
                        pnCloseModal();
                    };
                    reader.readAsDataURL(blob);
                }, 'image/jpeg', 0.9);
            }
            if (pnBox) pnBox.addEventListener('click', function () { if (pnFileInput) pnFileInput.click(); });
            if (pnFileInput) pnFileInput.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (file && file.type.match(/^image\//)) pnOpenModal(file);
            });
            if (pnBox) {
                pnBox.addEventListener('dragover', function (e) { e.preventDefault(); e.stopPropagation(); this.classList.add('dragover'); });
                pnBox.addEventListener('dragleave', function (e) { e.preventDefault(); this.classList.remove('dragover'); });
                pnBox.addEventListener('drop', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('dragover');
                    var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                    if (file && file.type.match(/^image\//)) pnOpenModal(file);
                });
            }
            if (pnCloseBtn) pnCloseBtn.addEventListener('click', pnCloseModal);
            if (pnCancelBtn) pnCancelBtn.addEventListener('click', pnCloseModal);
            if (pnConfirmBtn) pnConfirmBtn.addEventListener('click', pnApplyCrop);
            if (pnModal && pnModal.querySelector('.hero-crop-modal__backdrop')) pnModal.querySelector('.hero-crop-modal__backdrop').addEventListener('click', pnCloseModal);
        })();

        function alumniEsc(s) { if (s == null) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function addAlumniRow(data) {
            data = data || {};
            var message = alumniEsc(data.message || '');
            var position = alumniEsc(data.position || '');
            var workplace = alumniEsc(data.workplace || '');
            var graduationYear = alumniEsc(data.graduation_year || '');
            var photoPath = (data.photo_path || data.photo_url || '').toString();
            if (photoPath && photoPath.indexOf('http') === 0) { photoPath = ''; }
            var photoSrc = photoPath ? (serveBase + '/serve/uploads/' + photoPath.replace(/^\/+/, '')) : '';
            var row = document.createElement('div');
            row.className = 'alumni-row';
            row.innerHTML =
                '<div class="alumni-row__photo">' +
                '<img class="alumni-photo-preview" src="' + (photoSrc || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 80 80\'%3E%3Crect fill=\'%23eee\' width=\'80\' height=\'80\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23999\' font-size=\'10\'%3Eไม่มีรูป%3C/text%3E%3C/svg%3E') + '" alt="" style="max-width:80px;max-height:80px;">' +
                '<input type="hidden" class="alumni-photo-path" value="' + alumniEsc(photoPath) + '">' +
                '<input type="file" class="alumni-photo-input form-control form-control-sm" accept="image/*" style="margin-top:0.35rem;">' +
                '</div>' +
                '<div class="alumni-row__fields">' +
                '<div class="form-group full-width"><label class="form-label">ข้อความที่ฝากถึง / คำพูด</label><textarea class="form-control alumni-field alumni-message" rows="3" placeholder="ข้อความหรือคำพูดที่ต้องการบอก">' + message + '</textarea></div>' +
                '<div class="form-group"><label class="form-label">ตำแหน่งงานปัจจุบัน</label><input type="text" class="form-control alumni-field alumni-position" value="' + position + '" placeholder="ตำแหน่ง"></div>' +
                '<div class="form-group"><label class="form-label">สถานที่ทำงาน</label><input type="text" class="form-control alumni-field alumni-workplace" value="' + workplace + '" placeholder="หน่วยงาน/บริษัท"></div>' +
                '<div class="form-group"><label class="form-label">ปีที่จบการศึกษา</label><input type="text" class="form-control alumni-field alumni-graduation-year" value="' + graduationYear + '" placeholder="พ.ศ. หรือ ค.ศ."></div>' +
                '</div>' +
                '<div class="alumni-row__actions"><button type="button" class="btn btn-danger btn-sm alumni-remove-btn">ลบ</button></div>';
            alumniList.appendChild(row);
            row.querySelector('.alumni-remove-btn').addEventListener('click', function () { row.remove(); });
            row.querySelector('.alumni-photo-input').addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;
                openAlumniCropModal(file, row);
                this.value = '';
            });
        }
        function buildAlumniJson() {
            var rows = alumniList.querySelectorAll('.alumni-row');
            var arr = [];
            rows.forEach(function (row) {
                var message = (row.querySelector('.alumni-message') && row.querySelector('.alumni-message').value) || '';
                var position = (row.querySelector('.alumni-position') && row.querySelector('.alumni-position').value) || '';
                var workplace = (row.querySelector('.alumni-workplace') && row.querySelector('.alumni-workplace').value) || '';
                var graduationYear = (row.querySelector('.alumni-graduation-year') && row.querySelector('.alumni-graduation-year').value) || '';
                var photoPath = (row.querySelector('.alumni-photo-path') && row.querySelector('.alumni-photo-path').value) || '';
                arr.push({ message: message, position: position, workplace: workplace, graduation_year: graduationYear, photo_path: photoPath });
            });
            return JSON.stringify(arr);
        }
        function showAlumniMsg(msg, isError) {
            var el = document.getElementById('alumni-ajax-msg');
            if (el) { el.textContent = msg; el.style.color = isError ? 'var(--color-error)' : 'var(--secondary)'; }
        }
        document.getElementById('alumni-save-ajax-btn') && document.getElementById('alumni-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var json = buildAlumniJson();
            btn.disabled = true;
            showAlumniMsg('กำลังบันทึก...');
            var fd = new FormData();
            fd.append('alumni_messages_json', json);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    showAlumniMsg(res.success ? 'บันทึกศิษย์เก่าถึงรุ่นน้องเรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'), !res.success);
                })
                .catch(function () { btn.disabled = false; showAlumniMsg('เกิดข้อผิดพลาดในการเชื่อมต่อ', true); });
        });
        var alumniInitial = [];
        try {
            var raw = alumniList.getAttribute('data-initial') || '[]';
            alumniInitial = JSON.parse(raw);
            if (!Array.isArray(alumniInitial)) alumniInitial = [];
        } catch (e) { alumniInitial = []; }
        if (alumniInitial.length === 0) {
            addAlumniRow({});
        } else {
            alumniInitial.forEach(function (item) {
                var path = item.photo_path || (item.photo_url && item.photo_url.indexOf('http') !== 0 ? item.photo_url : '') || '';
                addAlumniRow({ message: item.message, position: item.position, workplace: item.workplace, graduation_year: item.graduation_year, photo_path: path });
            });
        }
        document.getElementById('alumni-add-btn') && document.getElementById('alumni-add-btn').addEventListener('click', function () { addAlumniRow({}); });
    }

    // --- หลักสูตร/แผนการเรียน: repeater ปี > ภาคเรียน > วิชา + บันทึก Ajax ---
    var curriculumList = document.getElementById('curriculum-list');
    var curriculumJsonField = document.getElementById('curriculum_json');
    if (curriculumList && curriculumJsonField) {
        function esc(s) { if (s == null) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function addCourseRow(semBody, c) {
            c = c || {};
            var tr = document.createElement('tr');
            tr.className = 'curriculum-course-row';
            tr.innerHTML = '<td><input type="text" class="form-control form-control-sm curriculum-course-code" value="' + esc(c.code || '') + '" placeholder="รหัส"></td>' +
                '<td><input type="text" class="form-control form-control-sm curriculum-course-name" value="' + esc(c.name || '') + '" placeholder="ชื่อวิชา"></td>' +
                '<td><input type="number" class="form-control form-control-sm curriculum-course-credits" value="' + esc(c.credits ?? '') + '" placeholder="0" min="0" style="width:4rem"></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm curriculum-remove-course">ลบ</button></td>';
            semBody.appendChild(tr);
            tr.querySelector('.curriculum-remove-course').addEventListener('click', function () { tr.remove(); });
        }
        function addSemester(yearCard, sem) {
            sem = sem || {};
            var semDiv = document.createElement('div');
            semDiv.className = 'curriculum-semester';
            semDiv.innerHTML = '<div class="curriculum-semester__head"><label class="form-label">ภาคเรียน</label><input type="text" class="form-control form-control-sm curriculum-semester-name" value="' + esc(sem.name || '') + '" placeholder="ภาคเรียนที่ 1"><button type="button" class="btn btn-danger btn-sm curriculum-remove-semester">ลบภาคเรียน</button></div>' +
                '<table class="curriculum-course-table"><thead><tr><th>รหัส</th><th>ชื่อวิชา</th><th>หน่วยกิต</th><th></th></tr></thead><tbody class="curriculum-semester-body"></tbody></table>' +
                '<button type="button" class="btn btn-outline btn-sm curriculum-add-course">+ เพิ่มวิชา</button>';
            var tbody = semDiv.querySelector('.curriculum-semester-body');
            (sem.courses || []).forEach(function (c) { addCourseRow(tbody, c); });
            if (!(sem.courses && sem.courses.length)) addCourseRow(tbody, {});
            yearCard.querySelector('.curriculum-year-body').appendChild(semDiv);
            semDiv.querySelector('.curriculum-add-course').addEventListener('click', function () { addCourseRow(tbody, {}); });
            semDiv.querySelector('.curriculum-remove-semester').addEventListener('click', function () { semDiv.remove(); });
        }
        function addYearCard(data) {
            data = data || {};
            var year = data.year || (curriculumList.querySelectorAll('.curriculum-year-card').length + 1);
            var title = esc(data.title || '');
            var credits = esc(data.total_credits ?? '');
            var card = document.createElement('div');
            card.className = 'curriculum-year-card';
            card.innerHTML = '<div class="curriculum-year-head"><h5 class="curriculum-year-title">ปีที่ ' + year + '</h5><button type="button" class="btn btn-danger btn-sm curriculum-remove-year">ลบปี</button></div>' +
                '<div class="form-row form-row--2"><div class="form-group"><label class="form-label">ชื่อช่วงปี</label><input type="text" class="form-control curriculum-year-title-input" value="' + title + '" placeholder="ชั้นปีที่ 1"></div>' +
                '<div class="form-group"><label class="form-label">หน่วยกิตรวม</label><input type="number" class="form-control curriculum-year-credits" value="' + credits + '" placeholder="18" min="0" style="width:6rem"></div></div>' +
                '<div class="curriculum-year-body"></div>' +
                '<button type="button" class="btn btn-outline btn-sm curriculum-add-semester">+ เพิ่มภาคเรียน</button>';
            curriculumList.appendChild(card);
            (data.semesters || []).forEach(function (s) { addSemester(card, s); });
            if (!(data.semesters && data.semesters.length)) addSemester(card, {});
            card.querySelector('.curriculum-add-semester').addEventListener('click', function () { addSemester(card, {}); });
            card.querySelector('.curriculum-remove-year').addEventListener('click', function () { card.remove(); });
        }
        function buildCurriculumJson() {
            var years = [];
            curriculumList.querySelectorAll('.curriculum-year-card').forEach(function (card, i) {
                var y = i + 1;
                var title = (card.querySelector('.curriculum-year-title-input') && card.querySelector('.curriculum-year-title-input').value) || ('ชั้นปีที่ ' + y);
                var total = parseInt((card.querySelector('.curriculum-year-credits') && card.querySelector('.curriculum-year-credits').value) || 0, 10) || 0;
                var semesters = [];
                card.querySelectorAll('.curriculum-semester').forEach(function (semEl) {
                    var name = (semEl.querySelector('.curriculum-semester-name') && semEl.querySelector('.curriculum-semester-name').value) || '';
                    var courses = [];
                    semEl.querySelectorAll('.curriculum-course-row').forEach(function (row) {
                        var code = (row.querySelector('.curriculum-course-code') && row.querySelector('.curriculum-course-code').value) || '';
                        var nameC = (row.querySelector('.curriculum-course-name') && row.querySelector('.curriculum-course-name').value) || '';
                        var cred = parseInt((row.querySelector('.curriculum-course-credits') && row.querySelector('.curriculum-course-credits').value) || 0, 10) || 0;
                        courses.push({ code: code, name: nameC, credits: cred });
                    });
                    semesters.push({ name: name, courses: courses });
                });
                years.push({ year: y, title: title, total_credits: total, semesters: semesters });
            });
            curriculumJsonField.value = JSON.stringify(years);
            return curriculumJsonField.value;
        }
        var curriculumInitial = [];
        try {
            var rawC = curriculumList.getAttribute('data-initial') || '[]';
            curriculumInitial = JSON.parse(rawC);
            if (!Array.isArray(curriculumInitial)) curriculumInitial = [];
        } catch (e) { curriculumInitial = []; }
        if (curriculumInitial.length === 0) addYearCard({});
        else curriculumInitial.forEach(function (y) { addYearCard(y); });
        document.getElementById('curriculum-add-year-btn') && document.getElementById('curriculum-add-year-btn').addEventListener('click', function () { addYearCard({}); });
        function showCurriculumMsg(msg, err) {
            var el = document.getElementById('curriculum-ajax-msg');
            if (el) { el.textContent = msg; el.style.color = err ? 'var(--color-error)' : 'var(--secondary)'; }
        }
        document.getElementById('curriculum-save-ajax-btn') && document.getElementById('curriculum-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var json = buildCurriculumJson();
            btn.disabled = true;
            showCurriculumMsg('กำลังบันทึก...');
            var fd = new FormData();
            fd.append('curriculum_json', json);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) { btn.disabled = false; showCurriculumMsg(res.success ? 'บันทึกแผนการเรียนเรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'), !res.success); })
                .catch(function () { btn.disabled = false; showCurriculumMsg('เกิดข้อผิดพลาดในการเชื่อมต่อ', true); });
        });
        window.buildCurriculumJson = buildCurriculumJson;
        contentForm.addEventListener('submit', function () { buildCurriculumJson(); });
    }

    // --- Toolbar แทรกข้อความ: ใช้กับโครงสร้างหลักสูตร และแผนการเรียน/อาชีพ/ค่าเล่าเรียน/การรับสมัคร/ข้อมูลติดต่อ ---
    function applyStructureTool(btn, targetTextarea) {
        if (!targetTextarea) return;
        var insert = btn.getAttribute('data-insert') || '';
        var start = targetTextarea.selectionStart, end = targetTextarea.selectionEnd, val = targetTextarea.value;
        targetTextarea.value = val.substring(0, start) + insert + val.substring(end);
        targetTextarea.selectionStart = targetTextarea.selectionEnd = start + insert.length;
        targetTextarea.focus();
    }
    document.querySelectorAll('.structure-tool').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = this.closest('.form-group') || this.closest('.content-with-toolbar');
            var ta = group ? group.querySelector('textarea') : null;
            if (ta) applyStructureTool(this, ta);
        });
    });
    var structureTa = document.getElementById('curriculum_structure');
    if (structureTa) {
        document.getElementById('curriculum-structure-save-ajax-btn') && document.getElementById('curriculum-structure-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var msgEl = document.getElementById('curriculum-structure-ajax-msg');
            if (typeof window.syncPtbField === 'function') window.syncPtbField('curriculum_structure');
            btn.disabled = true;
            if (msgEl) msgEl.textContent = 'กำลังบันทึก...';
            var fd = new FormData();
            fd.append('curriculum_structure', structureTa.value);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    if (msgEl) { msgEl.textContent = res.success ? 'บันทึกโครงสร้างหลักสูตรเรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'); msgEl.style.color = res.success ? 'var(--secondary)' : 'var(--color-error)'; }
                })
                .catch(function () { btn.disabled = false; if (msgEl) msgEl.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ'; });
        });
    }
})();
</script>
<script>
(function () {
    var programId = <?= (int) ($program['id'] ?? 0) ?>;
    var form = document.getElementById('content-page-form');
    var msg = document.getElementById('bundle-import-msg');
    var wrap = document.getElementById('bundle-preview-wrap');
    var grid = document.getElementById('bundle-compare-grid');
    var commitRow = document.getElementById('bundle-commit-row');
    var commitBtn = document.getElementById('bundle-import-commit-btn');
    var previewBtn = document.getElementById('bundle-import-preview-btn');
    var fileInput = document.getElementById('bundle-file-input');
    var currentBtn = document.getElementById('bundle-preview-current-btn');
    var errorsList = document.getElementById('bundle-import-errors');
    var bundleToken = null;

    function clearErrors() {
        if (!errorsList) return;
        errorsList.innerHTML = '';
        errorsList.style.display = 'none';
    }
    function showErrors(errors) {
        if (!errorsList) return;
        if (!errors || !errors.length) { clearErrors(); return; }
        var h = '';
        errors.forEach(function (e) { h += '<li>' + esc(e) + '</li>'; });
        errorsList.innerHTML = h;
        errorsList.style.display = 'block';
    }

    function csrfPair() {
        if (!form) return { name: '', value: '' };
        var inp = form.querySelector('input[name="csrf_test_name"]') || form.querySelector('input[type="hidden"][name*="csrf"]');
        return inp ? { name: inp.name, value: inp.value } : { name: '', value: '' };
    }
    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function renderSections(sections) {
        if (!sections || !sections.length) return '<p style="color:var(--color-gray-500);">—</p>';
        var h = '';
        sections.forEach(function (sec) {
            h += '<div style="margin-bottom:0.5rem; padding-bottom:0.35rem; border-bottom:1px dashed var(--color-gray-200);"><div style="font-weight:600; margin-bottom:0.2rem;">' + esc(sec.title) + '</div>';
            (sec.items || []).forEach(function (it) {
                h += '<div style="font-size:0.78rem; line-height:1.4;"><span style="color:var(--color-gray-600);">' + esc(it.label) + '</span><br><span style="word-break:break-word;">' + esc(it.value) + '</span></div>';
            });
            h += '</div>';
        });
        return h;
    }
    if (currentBtn) {
        currentBtn.addEventListener('click', function () {
            if (msg) { msg.textContent = 'กำลังโหลด...'; msg.style.color = ''; }
            clearErrors();
            fetch('<?= base_url('program-admin/bundle-preview/') ?>' + programId, { method: 'GET', credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) { if (msg) { msg.textContent = res.message || 'ผิดพลาด'; msg.style.color = 'var(--color-error)'; } return; }
                    if (grid) grid.innerHTML = '<div>' + renderSections(res.sections) + '</div><div style="color:var(--color-gray-500); font-size:0.75rem;">(ฝั่งขวา: นำเข้า — ยังไม่มี)</div>';
                    if (wrap) wrap.style.display = 'block';
                    if (msg) { msg.textContent = 'ฐานปัจจุบัน'; msg.style.color = 'var(--secondary)'; }
                    if (commitRow) commitRow.style.display = 'none';
                    bundleToken = null;
                })
                .catch(function () { if (msg) { msg.textContent = 'เชื่อมต่อไม่สำเร็จ'; msg.style.color = 'var(--color-error)'; } });
        });
    }
    if (previewBtn && fileInput) {
        previewBtn.addEventListener('click', function () {
            var f = fileInput.files && fileInput.files[0];
            if (!f) { if (msg) { msg.textContent = 'เลือกไฟล์ .json ก่อน'; msg.style.color = 'var(--color-error)'; } return; }
            var c = csrfPair();
            var fd = new FormData();
            fd.append('bundle_file', f);
            if (c.name) fd.append(c.name, c.value);
            if (msg) { msg.textContent = 'กำลังตรวจ...'; msg.style.color = ''; }
            clearErrors();
            if (commitRow) commitRow.style.display = 'none';
            bundleToken = null;
            fetch('<?= base_url('program-admin/bundle-import-preview/') ?>' + programId, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) {
                        if (msg) { msg.textContent = res.message || 'นำเข้าไม่ผ่าน'; msg.style.color = 'var(--color-error)'; }
                        showErrors(res.errors);
                        return;
                    }
                    bundleToken = res.token;
                    if (grid) {
                        grid.innerHTML = '<div><div style="font-weight:600; margin-bottom:0.25rem; color:var(--color-gray-800);">ฐานปัจจุบัน (ต่อหัวข้อ)</div>' + renderSections(res.current_sections) + '</div>' +
                            '<div><div style="font-weight:600; margin-bottom:0.25rem; color:var(--color-primary);">สิ่งที่นำเข้า (ก่อนบันทึก)</div>' + renderSections(res.preview_sections) + '</div>';
                    }
                    if (wrap) wrap.style.display = 'block';
                    if (commitRow) commitRow.style.display = 'block';
                    if (msg) {
                        msg.textContent = 'ตรวจผ่าน — sha1: ' + (res.file_sha1 || '').slice(0, 12) + '… กด "ยืนยันบันทึก" หรืออัปโหลดไฟล์ใหม่เพื่อล้าง';
                        msg.style.color = 'var(--secondary)';
                    }
                })
                .catch(function () { if (msg) { msg.textContent = 'เชื่อมต่อไม่สำเร็จ'; msg.style.color = 'var(--color-error)'; } });
        });
    }
    if (commitBtn) {
        commitBtn.addEventListener('click', function () {
            if (!bundleToken) { if (msg) { msg.textContent = 'ยังไม่มี token ให้รัน ตรวจก่อนนำเข้า อีกครั้ง'; msg.style.color = 'var(--color-error)'; } return; }
            var c = csrfPair();
            var fd = new FormData();
            fd.append('token', bundleToken);
            if (c.name) fd.append(c.name, c.value);
            if (msg) { msg.textContent = 'กำลังบันทึก...'; msg.style.color = ''; }
            clearErrors();
            commitBtn.disabled = true;
            fetch('<?= base_url('program-admin/bundle-import-commit/') ?>' + programId, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    commitBtn.disabled = false;
                    if (!res.success) {
                        if (msg) { msg.textContent = res.message || 'บันทึกไม่สำเร็จ'; msg.style.color = 'var(--color-error)'; }
                        showErrors(res.errors);
                        return;
                    }
                    if (msg) { msg.textContent = res.message || 'สำเร็จ'; msg.style.color = 'var(--secondary)'; }
                    if (commitRow) commitRow.style.display = 'none';
                    bundleToken = null;
                    if (res.success) window.location.reload();
                })
                .catch(function () { commitBtn.disabled = false; if (msg) { msg.textContent = 'เชื่อมต่อไม่สำเร็จ'; msg.style.color = 'var(--color-error)'; } });
        });
    }
})();
</script>

<?= $this->endSection() ?>