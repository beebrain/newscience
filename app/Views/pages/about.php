<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">เกี่ยวกับคณะ</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>เกี่ยวกับคณะ</span>
        </div>
    </div>
</section>

<!-- About Tabs -->
<section class="section">
    <div class="container">
        <div class="about-tabs" id="aboutTabs">
            <!-- Tab Navigation -->
            <div class="about-tabs__nav" role="tablist" aria-label="เกี่ยวกับคณะ">
                <button type="button" class="about-tabs__btn is-active" role="tab" aria-selected="true" data-tab="history">
                    <span class="about-tabs__btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </span>
                    <span class="about-tabs__btn-text">ประวัติคณะ</span>
                </button>
                <button type="button" class="about-tabs__btn" role="tab" aria-selected="false" data-tab="philosophy">
                    <span class="about-tabs__btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z" />
                        </svg>
                    </span>
                    <span class="about-tabs__btn-text">ปรัชญา &amp; วิสัยทัศน์</span>
                </button>
                <button type="button" class="about-tabs__btn" role="tab" aria-selected="false" data-tab="mission">
                    <span class="about-tabs__btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                        </svg>
                    </span>
                    <span class="about-tabs__btn-text">พันธกิจ &amp; นโยบาย</span>
                </button>
                <button type="button" class="about-tabs__btn" role="tab" aria-selected="false" data-tab="strategy">
                    <span class="about-tabs__btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <circle cx="12" cy="12" r="6" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>
                    </span>
                    <span class="about-tabs__btn-text">ยุทธศาสตร์</span>
                </button>
                <button type="button" class="about-tabs__btn" role="tab" aria-selected="false" data-tab="executives">
                    <span class="about-tabs__btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 00-3-3.87" />
                            <path d="M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </span>
                    <span class="about-tabs__btn-text">ทีมผู้บริหาร</span>
                </button>
            </div>

            <!-- Tab Panels -->
            <div class="about-tabs__panels">

                <!-- TAB 1: HISTORY (Timeline) -->
                <div class="about-tabs__panel is-active" role="tabpanel" data-panel="history">
                    <div class="section-header">
                        <span class="section-header__subtitle">ประวัติ</span>
                        <h2 class="section-header__title">เส้นทางแห่งความภาคภูมิ</h2>
                        <p class="section-header__description">ก้าวย่างของคณะวิทยาศาสตร์และเทคโนโลยี ตั้งแต่อดีตจนถึงปัจจุบัน</p>
                    </div>

                    <!-- CTA: อ่านประวัติฉบับเต็มแบบความเรียง -->
                    <div class="history-cta">
                        <button type="button" class="history-cta__btn" data-history-open aria-haspopup="dialog">
                            <span class="history-cta__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z" />
                                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z" />
                                </svg>
                            </span>
                            <span class="history-cta__text">
                                <strong>อ่านประวัติคณะฉบับเต็ม</strong>
                                <small>เนื้อหาแบบความเรียงต่อเนื่อง — ตั้งแต่ พ.ศ. ๒๕๑๘ ถึงปัจจุบัน</small>
                            </span>
                            <span class="history-cta__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </span>
                        </button>
                    </div>

                    <!-- Animated Timeline -->
                    <div class="timeline" id="timeline">
                        <div class="timeline__line"><span class="timeline__line-fill" id="timelineFill"></span></div>

                        <?php
                        // ประวัติคณะวิทยาศาสตร์และเทคโนโลยี มรอ. — แบ่งตามยุค
                        // era: ก่อตั้ง | จัดตั้งคณะ | ปฏิรูปหลักสูตร | ย้ายฐาน | วิทยาการข้อมูล | อนาคต
                        $timelineData = [
                            // ยุคที่ 1: ก่อตั้งและการเปลี่ยนแปลงฐานะ
                            ['year' => '๒๕๑๘', 'title' => 'หมวดวิชาวิทยาศาสตร์', 'desc' => 'เริ่มต้นจาก "หมวดวิชาวิทยาศาสตร์" ภายใต้วิทยาลัยครูอุตรดิตถ์ วางรากฐานการเรียนการสอนด้านวิทยาศาสตร์ในจังหวัดอุตรดิตถ์', 'era' => 'ก่อตั้ง', 'era_color' => '#1e3a5f', 'highlight' => false],
                            ['year' => '๒๕๓๕', 'title' => 'พระราชทานชื่อ "สถาบันราชภัฏ"', 'desc' => 'วิทยาลัยครูอุตรดิตถ์ได้รับพระราชทานชื่อใหม่เป็น "สถาบันราชภัฏอุตรดิตถ์" เป็นจุดเปลี่ยนสำคัญของการพัฒนาสถาบันการศึกษา', 'era' => 'ก่อตั้ง', 'era_color' => '#1e3a5f', 'highlight' => false],
                            ['year' => '๑๕ มิ.ย. ๒๕๔๗', 'title' => 'ยกฐานะเป็นมหาวิทยาลัยราชภัฏอุตรดิตถ์', 'desc' => 'ยกฐานะขึ้นเป็น "มหาวิทยาลัยราชภัฏอุตรดิตถ์" อย่างเป็นทางการ เมื่อวันที่ ๑๕ มิถุนายน ๒๕๔๗ เปิดศักราชใหม่ของการศึกษาระดับอุดมศึกษา', 'era' => 'ก่อตั้ง', 'era_color' => '#1e3a5f', 'highlight' => true],

                            // ยุคที่ 2: จัดตั้งคณะและขยายตัวทางวิชาการ
                            ['year' => '๒๕๔๘ – ๒๕๔๙', 'title' => 'ประกาศจัดตั้ง "คณะวิทยาศาสตร์และเทคโนโลยี"', 'desc' => 'ประกาศจัดตั้งคณะวิทยาศาสตร์และเทคโนโลยีอย่างเป็นทางการ แบ่งส่วนราชการภายในเป็น ๑๐ หลักสูตรสาขาวิชา', 'era' => 'จัดตั้งคณะ', 'era_color' => '#0d9488', 'highlight' => true],
                            ['year' => '๒๕๕๐ – ๒๕๕๒', 'title' => 'เปิดบัณฑิตศึกษา ป.โท–ป.เอก', 'desc' => 'ขยายการศึกษาสู่ระดับบัณฑิตศึกษา (ปริญญาโทและปริญญาเอก) ในสาขาพลังงาน สิ่งแวดล้อม และคณิตศาสตร์', 'era' => 'จัดตั้งคณะ', 'era_color' => '#0d9488', 'highlight' => false],

                            // ยุคที่ 3: ปฏิรูปหลักสูตรตามมาตรฐาน TQF
                            ['year' => '๒๕๕๕', 'title' => 'ปฏิรูปหลักสูตรเข้าสู่กรอบ TQF', 'desc' => 'ปรับปรุงหลักสูตรเดิม ๑๐ หลักสูตรให้เข้าสู่กรอบมาตรฐานคุณวุฒิระดับอุดมศึกษา (TQF) และเปิดสาขาเทคโนโลยีชีวภาพเพิ่มเติม', 'era' => 'ปฏิรูปหลักสูตร', 'era_color' => '#7c3aed', 'highlight' => false],
                            ['year' => '๒๕๕๗', 'title' => 'บูรณาการข้ามคณะ', 'desc' => 'บูรณาการกับคณะครุศาสตร์พัฒนาหลักสูตร ค.บ. (วิทยาศาสตร์และพลศึกษา) และร่วมกับคณะเกษตรฯ พัฒนาหลักสูตรด้านทรัพยากรธรรมชาติ', 'era' => 'ปฏิรูปหลักสูตร', 'era_color' => '#7c3aed', 'highlight' => false],
                            ['year' => '๒๕๕๘', 'title' => 'หลักสูตรใหม่ด้านคอมพิวเตอร์และพลังงาน', 'desc' => 'เปิดหลักสูตรใหม่ด้านวิทยาการคอมพิวเตอร์ประยุกต์ และสาขาพลังงานเพิ่มเติม เพื่อตอบสนองความต้องการของตลาดแรงงาน', 'era' => 'ปฏิรูปหลักสูตร', 'era_color' => '#7c3aed', 'highlight' => false],

                            // ยุคที่ 4: ย้ายฐานสู่ทุ่งกะโล่
                            ['year' => '๒๕๕๙', 'title' => 'เริ่มย้ายฐานสู่ "ทุ่งกะโล่"', 'desc' => 'เริ่มย้ายสำนักงานคณบดีและบางสาขา (ฟิสิกส์, สาธารณสุข, สิ่งแวดล้อม, คอมพิวเตอร์, IT) ไปยังทุ่งกะโล่ ที่อาคาร GAB และศูนย์วิจัยพลังงาน', 'era' => 'ย้ายฐาน', 'era_color' => '#2d7d46', 'highlight' => false],
                            ['year' => '๒๕๖๒', 'title' => 'อาคาร STB และ STC เปิดใช้งาน', 'desc' => 'สาขาเคมีและชีววิทยาย้ายเข้าสู่อาคารใหม่ (STB และ STC) ณ ทุ่งกะโล่ พร้อมห้องปฏิบัติการมาตรฐาน', 'era' => 'ย้ายฐาน', 'era_color' => '#2d7d46', 'highlight' => false],
                            ['year' => '๗ พ.ค. ๒๕๖๔', 'title' => 'อาคาร STA เปิดใช้งานสมบูรณ์', 'desc' => 'สำนักงานคณบดีและสาขาที่เหลือย้ายเข้าอาคาร STA อย่างสมบูรณ์ในวันที่ ๗ พฤษภาคม ๒๕๖๔ รวมศูนย์การบริหารและการเรียนการสอนไว้ที่ทุ่งกะโล่', 'era' => 'ย้ายฐาน', 'era_color' => '#2d7d46', 'highlight' => true],

                            // ยุคที่ 5: วิทยาการข้อมูลและการเรียนรู้ตลอดชีวิต
                            ['year' => '๒๕๖๕', 'title' => 'เปิดสาขา Data Science + URU MOOC', 'desc' => 'เปิดสอนสาขาวิทยาการข้อมูล (Data Science) และเริ่มทำหลักสูตร Life Long Learning (URU MOOC) เช่น หลักสูตรอาหารพื้นถิ่นและเบเกอรี่', 'era' => 'วิทยาการข้อมูล', 'era_color' => '#eab308', 'highlight' => false],
                            ['year' => '๒๕๖๖ – ๒๕๖๗', 'title' => 'เครือข่าย MOU กับโรงเรียน', 'desc' => 'ขยายความร่วมมือ (MOU) กับโรงเรียนเครือข่าย เช่น โรงเรียนน้ำริด, ลับแลพิทยาคม, เตรียมอุดมศึกษา และโรงเรียนอุตรดิตถ์ เชื่อมโยงการศึกษาท้องถิ่น', 'era' => 'วิทยาการข้อมูล', 'era_color' => '#eab308', 'highlight' => false],
                            ['year' => '๒๙ ส.ค. ๒๕๖๘', 'title' => 'รับเสด็จ ฯ ที่อาคาร STA', 'desc' => 'คณะฯ ได้รับเกียรติให้ใช้อาคาร STA เป็นพื้นที่รับเสด็จ สมเด็จพระกนิษฐาธิราชเจ้า กรมสมเด็จพระเทพรัตนราชสุดาฯ ในโอกาสเสด็จเปิดอาคารรักษ์สุขชีวิน พร้อมพัฒนาหลักสูตร ป.โท–ป.เอก สาขาวิทยาศาสตร์ประยุกต์', 'era' => 'วิทยาการข้อมูล', 'era_color' => '#eab308', 'highlight' => true],

                            // ยุคที่ 6: ก้าวสู่ปัจจุบันและอนาคต
                            ['year' => '๒๕๖๙', 'title' => 'มุ่งสู่ Outcome-Based Education (OBE)', 'desc' => 'ดำเนินการปรับปรุงหลักสูตรตามเกณฑ์ Outcome-Based Education (OBE) มุ่งเน้นผลลัพธ์การเรียนรู้ของผู้เรียนเป็นสำคัญ เตรียมพร้อมสำหรับนักศึกษาปีการศึกษา ๒๕๗๐', 'era' => 'อนาคต', 'era_color' => '#f97316', 'highlight' => true],
                        ];

                        // เก็บยุคปัจจุบันไว้ตรวจว่าควร render era divider ใหม่หรือไม่
                        $currentEra = null;
                        ?>

                        <?php foreach ($timelineData as $i => $item): ?>
                            <?php $isNewEra = ($currentEra !== $item['era']);
                            $currentEra = $item['era']; ?>

                            <?php if ($isNewEra): ?>
                                <div class="timeline__era-divider" data-tl-era>
                                    <span class="timeline__era-chip" style="--era-color: <?= esc($item['era_color']) ?>;">
                                        <span class="timeline__era-dot"></span>
                                        ยุค<?= esc($item['era']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="timeline__item <?= $i % 2 === 0 ? 'timeline__item--left' : 'timeline__item--right' ?><?= !empty($item['highlight']) ? ' timeline__item--highlight' : '' ?>"
                                data-tl-index="<?= $i ?>"
                                style="--era-color: <?= esc($item['era_color']) ?>;">
                                <div class="timeline__dot" aria-hidden="true">
                                    <span class="timeline__dot-pulse"></span>
                                </div>
                                <div class="timeline__card">
                                    <?php if (!empty($item['highlight'])): ?>
                                        <span class="timeline__star" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z" />
                                            </svg>
                                        </span>
                                    <?php endif; ?>
                                    <div class="timeline__year"><?= esc($item['year']) ?></div>
                                    <h3 class="timeline__title"><?= esc($item['title']) ?></h3>
                                    <p class="timeline__desc"><?= esc($item['desc']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- CTA ซ้ำที่ด้านล่าง — เผื่อผู้ใช้อ่านครบ timeline แล้วอยากอ่านความเรียง -->
                    <div class="history-cta history-cta--bottom">
                        <button type="button" class="history-cta__btn" data-history-open aria-haspopup="dialog">
                            <span class="history-cta__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z" />
                                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z" />
                                </svg>
                            </span>
                            <span class="history-cta__text">
                                <strong>อ่านประวัติคณะฉบับเต็ม</strong>
                                <small>เรียบเรียงเป็นความเรียง พร้อมรายละเอียดเชิงลึก</small>
                            </span>
                            <span class="history-cta__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </span>
                        </button>
                    </div>

                    <!-- History Modal — ความเรียงประวัติคณะฉบับเต็ม (รวมก้อนเดียว) -->
                    <?php
                    // ===== ข้อความประวัติฉบับเต็ม (Dummy) — แทนที่ส่วนนี้ด้วยเนื้อหาจริงในภายหลัง =====
                    // ใช้ \n\n คั่นย่อหน้า; รองรับการเขียนต่อเนื่องยาว
                    $historyFullProse = <<<PROSE
วิวัฒนาการแห่งการเรียนรู้และนวัตกรรม: ประวัติความเป็นมาของคณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์

คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ มีรากฐานการก่อกำเนิดที่เชื่อมโยงกับประวัติศาสตร์การผลิตครูของประเทศไทย โดยเริ่มต้นจากการเป็น "หมวดวิทยาศาสตร์" ภายใต้พระราชบัญญัติวิทยาลัยครู พ.ศ. 2518 และได้เริ่มทำหน้าที่ผลิตบัณฑิตสายครูตามหลักสูตรของสภาการฝึกหัดครูในปีถัดมา จนกระทั่งก้าวย่างสำคัญเกิดขึ้นในปี พ.ศ. 2535 เมื่อได้รับพระมหากรุณาธิคุณโปรดเกล้าฯ พระราชทานนาม "สถาบันราชภัฏ" และได้ยกฐานะขึ้นเป็น "มหาวิทยาลัยราชภัฏอุตรดิตถ์" อย่างสมบูรณ์เมื่อวันที่ 15 มิถุนายน พ.ศ. 2547 นำมาสู่การจัดตั้งคณะวิทยาศาสตร์และเทคโนโลยีเป็นส่วนราชการอย่างเป็นทางการตามกฎกระทรวงในปี พ.ศ. 2548

ในช่วงทศวรรษแรกของการก่อตั้ง คณะวิทยาศาสตร์และเทคโนโลยีได้มุ่งเน้นการวางรากฐานทางวิชาการที่เข้มแข็ง โดยเริ่มจากการบริหารจัดการหลักสูตรระดับปริญญาตรี 10 สาขาวิชาที่ครอบคลุมทั้งวิทยาศาสตร์ประยุกต์และเทคโนโลยี ต่อมาในช่วงปี พ.ศ. 2550 เป็นต้นมา คณะฯ ได้ขยายขอบเขตการศึกษาเข้าสู่ระดับบัณฑิตศึกษา ทั้งในระดับปริญญาโทและปริญญาเอก เพื่อสร้างองค์ความรู้ขั้นสูงในด้านพลังงาน สิ่งแวดล้อม และคณิตศาสตร์ศึกษา พร้อมทั้งปรับปรุงหลักสูตรทั้งหมดให้เข้าสู่กรอบมาตรฐานคุณวุฒิระดับอุดมศึกษา (TQF) ในปี พ.ศ. 2555 เพื่อยกระดับคุณภาพการศึกษาให้เป็นที่ยอมรับในระดับสากล

จุดเปลี่ยนสำคัญด้านโครงสร้างพื้นฐานเกิดขึ้นในปี พ.ศ. 2559 เมื่อคณะฯ เริ่มดำเนินการย้ายส่วนงานบริหารและสาขาวิชาต่าง ๆ จากพื้นที่เดิมเข้าสู่พื้นที่มหาวิทยาลัยราชภัฏอุตรดิตถ์ ลำรางทุ่งกะโล่ โดยมีการจัดสรรอาคารปฏิบัติงานที่ทันสมัย ได้แก่ อาคาร STA, STB และ STC ซึ่งกระบวนการย้ายพื้นที่นี้เสร็จสิ้นอย่างสมบูรณ์ในปี พ.ศ. 2564 ทำให้พื้นที่ทุ่งกะโล่กลายเป็นศูนย์กลางแห่งการเรียนรู้และวิจัยทางวิทยาศาสตร์ที่ครบวงจร รองรับการเติบโตของสาขาวิชาใหม่ ๆ อย่างเช่น วิทยาการข้อมูล (Data Science) ซึ่งเปิดสอนในปี พ.ศ. 2565 เพื่อตอบสนองต่อการเปลี่ยนแปลงของโลกในยุคดิจิทัล

นอกเหนือจากการจัดการศึกษาในระบบ คณะวิทยาศาสตร์และเทคโนโลยียังให้ความสำคัญกับการเรียนรู้ตลอดชีวิต (Lifelong Learning) โดยการพัฒนาหลักสูตรระยะสั้นและระบบ URU MOOC เพื่อเชื่อมโยงองค์ความรู้สู่ชุมชน รวมถึงการสร้างเครือข่ายความร่วมมือทางวิชาการกับโรงเรียนมัธยมในพื้นที่จังหวัดอุตรดิตถ์อย่างต่อเนื่อง เพื่อบ่มเพาะเยาวชนให้มีทักษะด้านวิทยาศาสตร์และเทคโนโลยีตั้งแต่ระดับพื้นฐาน

ปี พ.ศ. 2568 ถือเป็นปีแห่งความสิริมงคลและภาคภูมิใจสูงสุดของคณะฯ เมื่อบุคลากรได้มีโอกาสปฏิบัติหน้าที่รับเสด็จสมเด็จพระกนิษฐาธิราชเจ้า กรมสมเด็จพระเทพรัตนราชสุดาฯ สยามบรมราชกุมารี ในวโรกาสเสด็จพระราชดำเนินเปิด "อาคารรักษ์สุขชีวิน" โดยคณะฯ ได้ถวายงานในพื้นที่อาคาร STA เพื่อใช้เป็นพื้นที่ส่วนพระองค์ในระหว่างทรงปฏิบัติพระราชกรณียกิจ

ปัจจุบัน ในปี พ.ศ. 2569 คณะวิทยาศาสตร์และเทคโนโลยียังคงไม่หยุดนิ่งในการพัฒนา โดยได้ก้าวเข้าสู่การปฏิรูปหลักสูตรตามเกณฑ์มาตรฐานที่มุ่งเน้นผลลัพธ์การเรียนรู้ของผู้เรียนเป็นสำคัญ (Outcome-Based Education: OBE) เพื่อเตรียมความพร้อมในการผลิตบัณฑิตรุ่นใหม่ในปีการศึกษา 2570 ให้เป็นผู้ที่มีความรู้คู่ทักษะ พร้อมขับเคลื่อนสังคมด้วยวิทยาศาสตร์ เทคโนโลยี และนวัตกรรมสืบต่อไปอย่างยั่งยืน
PROSE;
                    $fullProseParas = array_values(array_filter(array_map('trim', preg_split("/\r\n\r\n|\r\r|\n\n/", $historyFullProse))));
                    ?>

                    <div class="history-modal" id="historyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="historyModalTitle">
                        <div class="history-modal__backdrop" data-history-close></div>
                        <div class="history-modal__panel" role="document">
                            <button type="button" class="history-modal__close" data-history-close aria-label="ปิดหน้าต่าง">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>

                            <div class="history-modal__header">
                                <span class="history-modal__era">ประวัติฉบับเต็ม</span>
                                <span class="history-modal__year">พ.ศ. ๒๕๑๘ – ปัจจุบัน</span>
                                <h3 class="history-modal__title" id="historyModalTitle">ประวัติคณะวิทยาศาสตร์และเทคโนโลยี</h3>
                                <p class="history-modal__subtitle">มหาวิทยาลัยราชภัฏอุตรดิตถ์</p>
                            </div>

                            <div class="history-modal__divider"></div>

                            <div class="history-modal__body" id="historyModalBody">
                                <?php foreach ($fullProseParas as $p): ?>
                                    <p><?= nl2br(esc($p)) ?></p>
                                <?php endforeach; ?>
                            </div>

                            <div class="history-modal__footer">
                                <button type="button" class="btn btn-outline" data-history-close>ปิด</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PHILOSOPHY & VISION -->
                <div class="about-tabs__panel" role="tabpanel" data-panel="philosophy">
                    <div class="feature-section animate-on-scroll">
                        <div class="feature-section__image">
                            <img src="<?= base_url('assets/images/research_laboratory.png') ?>" alt="ปรัชญา">
                        </div>
                        <div class="feature-section__content">
                            <span class="feature-section__subtitle">ปรัชญา</span>
                            <h2 class="feature-section__title"><?= esc($philosophy ?? 'สร้างองค์ความรู้และพัฒนาคนในชาติ ด้วยวิทยาศาสตร์และเทคโนโลยี') ?></h2>
                        </div>
                    </div>

                    <div class="feature-section feature-section--reverse animate-on-scroll" style="margin-top:3rem;">
                        <div class="feature-section__image">
                            <img src="<?= base_url('assets/images/community_service.png') ?>" alt="วิสัยทัศน์">
                        </div>
                        <div class="feature-section__content">
                            <span class="feature-section__subtitle">วิสัยทัศน์</span>
                            <h2 class="feature-section__title">วิสัยทัศน์คณะ</h2>
                            <p class="feature-section__description">
                                <?php
                                $visionText = (string) ($vision ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มุ่งพัฒนาและผลิตบัณฑิตให้เป็นคนดี คนเก่ง มีจิตอาสา นำพาสังคม พร้อมทั้งเป็นแหล่งเรียนรู้และบริการวิชาการแก่ชุมชน ท้องถิ่น ระดับชาติและนานาชาติ');
                                $visionText = str_replace('องค์กรแห่งความสุข', '', $visionText);
                                $visionText = trim(preg_replace('/\s+/', ' ', $visionText) ?? $visionText);
                                ?>
                                <?= esc($visionText) ?>
                            </p>
                        </div>
                    </div>

                    <?php if (!empty($identity)): ?>
                        <div class="about-callout animate-on-scroll" style="margin-top:3rem;">
                            <span class="about-callout__label">อัตลักษณ์</span>
                            <p class="about-callout__text"><?= esc($identity) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: MISSION & POLICY -->
                <div class="about-tabs__panel" role="tabpanel" data-panel="mission">
                    <div class="section-header">
                        <span class="section-header__subtitle">พันธกิจ</span>
                        <h2 class="section-header__title">ภารกิจหลักของคณะ</h2>
                    </div>
                    <?php
                    $missionItems = !empty($mission) ? array_filter(array_map('trim', explode("\n", $mission))) : [];
                    ?>
                    <?php if (!empty($missionItems)): ?>
                        <ol class="about-list about-list--mission animate-on-scroll">
                            <?php foreach ($missionItems as $item): ?>
                                <li class="about-list__item"><?= esc($item) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p class="section-description">ผลิตบัณฑิต วิจัยและบริการวิชาการ ทำนุบำรุงศิลปวัฒนธรรม</p>
                    <?php endif; ?>

                    <?php if (!empty($policy)): ?>
                        <div class="section-header" style="margin-top:3.5rem;">
                            <span class="section-header__subtitle">นโยบาย</span>
                            <h2 class="section-header__title">นโยบายคณะ</h2>
                        </div>
                        <?php $policyItems = array_filter(array_map('trim', explode("\n", $policy))); ?>
                        <ol class="about-list about-list--policy animate-on-scroll">
                            <?php foreach ($policyItems as $item): ?>
                                <li class="about-list__item"><?= esc($item) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>

                <!-- TAB 4: STRATEGY -->
                <div class="about-tabs__panel" role="tabpanel" data-panel="strategy">
                    <div class="section-header">
                        <span class="section-header__subtitle">ยุทธศาสตร์</span>
                        <h2 class="section-header__title"><?= esc($strategy_title ?: 'ยุทธศาสตร์ในการบริหารคณะ') ?></h2>
                    </div>
                    <?php if (!empty($strategies)): ?>
                        <ul class="about-list about-list--strategy animate-on-scroll">
                            <?php foreach ($strategies as $s): ?>
                                <li class="about-list__item"><?= esc($s) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="section-description">ยังไม่มีข้อมูลยุทธศาสตร์</p>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="stats" style="margin-top:3rem;">
                        <div class="stat animate-on-scroll">
                            <div class="stat__number">๘๙</div>
                            <div class="stat__label">ปีแห่งความเป็นเลิศ</div>
                        </div>
                        <div class="stat animate-on-scroll">
                            <div class="stat__number">๑๓</div>
                            <div class="stat__label">หลักสูตร</div>
                        </div>
                        <div class="stat animate-on-scroll">
                            <div class="stat__number">๑๑</div>
                            <div class="stat__label">สาขาวิชา</div>
                        </div>
                        <div class="stat animate-on-scroll">
                            <div class="stat__number">๒๐๐๐+</div>
                            <div class="stat__label">นักศึกษา</div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: EXECUTIVES (Poster Slider — uploaded images) -->
                <div class="about-tabs__panel" role="tabpanel" data-panel="executives">
                    <div class="section-header">
                        <span class="section-header__subtitle">ผู้บริหาร</span>
                        <h2 class="section-header__title">ทีมผู้บริหารคณะ</h2>
                        <p class="section-header__description">เลื่อนดูโปสเตอร์แนะนำผู้บริหารแต่ละท่าน</p>
                    </div>

                    <?php $posters = $executive_posters ?? []; ?>

                    <?php if (empty($posters)): ?>
                        <div class="exec-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                            <h3>ยังไม่มีโปสเตอร์ผู้บริหาร</h3>
                            <p>ผู้ดูแลระบบสามารถอัปโหลดโปสเตอร์ได้ที่ <strong>Admin → โปสเตอร์ผู้บริหาร</strong></p>
                        </div>
                    <?php else: ?>
                        <div class="exec-slider" id="execSlider">
                            <button type="button" class="exec-slider__nav exec-slider__nav--prev" aria-label="ก่อนหน้า" id="execPrev">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </button>
                            <button type="button" class="exec-slider__nav exec-slider__nav--next" aria-label="ถัดไป" id="execNext">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </button>

                            <div class="exec-slider__viewport" id="execViewport">
                                <div class="exec-slider__track" id="execTrack">
                                    <?php foreach ($posters as $i => $p): ?>
                                        <?php
                                        $imgUrl = !empty($p['image']) ? image_manager_serve_url('executive_poster', $p['image']) : '';
                                        $linkUrl = !empty($p['link_url']) ? $p['link_url'] : null;
                                        $tag = $linkUrl ? 'a' : 'div';
                                        $extraAttr = $linkUrl ? ' href="' . esc($linkUrl) . '" target="_blank" rel="noopener"' : '';
                                        ?>
                                        <<?= $tag ?> class="exec-poster<?= $linkUrl ? ' exec-poster--linked' : '' ?>"<?= $extraAttr ?> data-index="<?= $i ?>">
                                            <div class="exec-poster__image">
                                                <?php if ($imgUrl !== ''): ?>
                                                    <img src="<?= esc($imgUrl) ?>" alt="<?= esc($p['title'] ?? '') ?>" loading="lazy">
                                                <?php else: ?>
                                                    <div class="exec-poster__placeholder">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($p['title']) || !empty($p['caption'])): ?>
                                                <div class="exec-poster__caption">
                                                    <?php if (!empty($p['title'])): ?>
                                                        <strong><?= esc($p['title']) ?></strong>
                                                    <?php endif; ?>
                                                    <?php if (!empty($p['caption'])): ?>
                                                        <span><?= esc($p['caption']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <span class="exec-poster__index"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                        </<?= $tag ?>>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="exec-slider__dots" id="execDots">
                                <?php foreach ($posters as $i => $_p): ?>
                                    <button type="button" class="exec-slider__dot<?= $i === 0 ? ' is-active' : '' ?>" data-go="<?= $i ?>" aria-label="สไลด์ที่ <?= $i + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="text-center" style="margin-top: 2rem;">
                        <a href="<?= base_url('personnel') ?>" class="btn btn-outline">ดูบุคลากรทั้งหมด</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">ติดต่อคณะ</h2>
        <p class="cta-section__description">
            <?= esc($site_info['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?> <?= esc($site_info['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?>
        </p>
        <a href="<?= base_url('contact') ?>" class="btn btn-secondary btn-lg">ติดต่อเรา</a>
    </div>
</section>

<style>
    /* ============ ABOUT TABS ============ */
    .about-tabs {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .about-tabs__nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        padding: 0.5rem;
    }

    .about-tabs__btn {
        flex: 1 1 auto;
        min-width: 160px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.95rem 1.1rem;
        border: none;
        background: transparent;
        color: #64748b;
        font-family: inherit;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        border-radius: 14px;
        transition: all 0.25s ease;
        position: relative;
    }

    .about-tabs__btn-icon {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .about-tabs__btn-icon svg {
        width: 100%;
        height: 100%;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .about-tabs__btn:hover {
        color: #1e3a5f;
        background: rgba(255, 255, 255, 0.6);
    }

    .about-tabs__btn.is-active {
        background: white;
        color: #1e3a5f;
        box-shadow: 0 6px 20px rgba(30, 58, 95, 0.12);
    }

    .about-tabs__btn.is-active::after {
        content: '';
        position: absolute;
        left: 20%;
        right: 20%;
        bottom: -0.5rem;
        height: 3px;
        border-radius: 2px;
        background: linear-gradient(135deg, #1e3a5f, #2d7d46);
    }

    .about-tabs__panels {
        padding: 3rem 2.25rem;
        min-height: 400px;
    }

    .about-tabs__panel {
        display: none;
        animation: aboutFadeIn 0.45s ease both;
    }

    .about-tabs__panel.is-active {
        display: block;
    }

    @keyframes aboutFadeIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ============ TIMELINE ============ */
    .timeline {
        position: relative;
        max-width: 1000px;
        margin: 3rem auto 0;
        padding: 1rem 0 2rem;
    }

    .timeline__line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 4px;
        transform: translateX(-50%);
        background: rgba(30, 58, 95, 0.08);
        border-radius: 4px;
        overflow: hidden;
    }

    .timeline__line-fill {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 0;
        background: linear-gradient(180deg, #1e3a5f 0%, #2d7d46 50%, #eab308 100%);
        border-radius: 4px;
        transition: height 0.15s linear;
        box-shadow: 0 0 18px rgba(45, 125, 70, 0.45);
    }

    .timeline__item {
        position: relative;
        width: 50%;
        padding: 1.2rem 2.5rem;
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .timeline__item.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .timeline__item--left {
        left: 0;
        text-align: right;
    }

    .timeline__item--right {
        left: 50%;
        text-align: left;
    }

    .timeline__dot {
        position: absolute;
        top: 1.8rem;
        width: 22px;
        height: 22px;
        background: white;
        border: 4px solid #2d7d46;
        border-radius: 50%;
        z-index: 2;
        box-shadow: 0 4px 14px rgba(45, 125, 70, 0.3);
    }

    .timeline__item--left .timeline__dot {
        right: -11px;
    }

    .timeline__item--right .timeline__dot {
        left: -11px;
    }

    .timeline__dot-pulse {
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        background: rgba(45, 125, 70, 0.35);
        opacity: 0;
    }

    .timeline__item.is-visible .timeline__dot-pulse {
        animation: timelinePulse 1.6s ease-out infinite;
    }

    @keyframes timelinePulse {
        0% {
            transform: scale(0.8);
            opacity: 0.7;
        }

        100% {
            transform: scale(2.2);
            opacity: 0;
        }
    }

    .timeline__card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        position: relative;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    /* ============ HISTORY CTA (เปิด Modal) ============ */
    .history-cta {
        max-width: 720px;
        margin: 2rem auto 0;
    }

    .history-cta--bottom {
        margin: 3rem auto 0;
    }

    .history-cta__btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d7d46 100%);
        color: white;
        border: none;
        border-radius: 18px;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        box-shadow: 0 12px 32px rgba(30, 58, 95, 0.25);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .history-cta__btn::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(234, 179, 8, 0.25), transparent 70%);
        pointer-events: none;
    }

    .history-cta__btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 42px rgba(30, 58, 95, 0.35);
    }

    .history-cta__btn:focus-visible {
        outline: 3px solid #eab308;
        outline-offset: 3px;
    }

    .history-cta__icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .history-cta__icon svg {
        width: 22px;
        height: 22px;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .history-cta__text {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .history-cta__text strong {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .history-cta__text small {
        font-size: 0.82rem;
        opacity: 0.85;
        font-weight: 400;
    }

    .history-cta__arrow {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }

    .history-cta__arrow svg {
        width: 16px;
        height: 16px;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .history-cta__btn:hover .history-cta__arrow {
        transform: translateX(4px);
        background: #eab308;
    }

    .timeline__card::before {
        content: '';
        position: absolute;
        top: 1.6rem;
        width: 16px;
        height: 16px;
        background: white;
        transform: rotate(45deg);
    }

    .timeline__item--left .timeline__card::before {
        right: -8px;
        box-shadow: 4px -4px 8px -3px rgba(15, 23, 42, 0.05);
    }

    .timeline__item--right .timeline__card::before {
        left: -8px;
        box-shadow: -4px 4px 8px -3px rgba(15, 23, 42, 0.05);
    }

    .timeline__card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    }

    .timeline__year {
        display: inline-block;
        padding: 0.3rem 0.85rem;
        background: var(--era-color, #1e3a5f);
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
        border-radius: 50px;
        margin-bottom: 0.7rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .timeline__title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.5rem;
    }

    .timeline__desc {
        color: #475569;
        line-height: 1.65;
        margin: 0;
        font-size: 0.95rem;
    }

    /* Color-code dots & accents by era */
    .timeline__item .timeline__dot {
        border-color: var(--era-color, #2d7d46);
        box-shadow: 0 4px 14px color-mix(in srgb, var(--era-color, #2d7d46) 35%, transparent);
    }

    .timeline__item .timeline__dot-pulse {
        background: color-mix(in srgb, var(--era-color, #2d7d46) 35%, transparent);
    }

    /* Highlight (key milestone) */
    .timeline__item--highlight .timeline__card {
        background: linear-gradient(135deg, #fff 0%, color-mix(in srgb, var(--era-color, #2d7d46) 7%, #fff) 100%);
        border: 2px solid color-mix(in srgb, var(--era-color, #2d7d46) 40%, transparent);
    }

    .timeline__item--highlight .timeline__dot {
        width: 28px;
        height: 28px;
        background: var(--era-color, #2d7d46);
        border-color: white;
    }

    .timeline__item--left.timeline__item--highlight .timeline__dot {
        right: -14px;
    }

    .timeline__item--right.timeline__item--highlight .timeline__dot {
        left: -14px;
    }

    .timeline__star {
        position: absolute;
        top: -10px;
        width: 32px;
        height: 32px;
        background: var(--era-color, #2d7d46);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 18px color-mix(in srgb, var(--era-color, #2d7d46) 50%, transparent);
        z-index: 3;
    }

    .timeline__star svg {
        width: 16px;
        height: 16px;
    }

    .timeline__item--left .timeline__card .timeline__star {
        right: 1rem;
    }

    .timeline__item--right .timeline__card .timeline__star {
        left: 1rem;
    }

    /* Era divider chip — sits in the center column on top of the line */
    .timeline__era-divider {
        position: relative;
        text-align: center;
        margin: 2rem 0 1rem;
        z-index: 3;
    }

    .timeline__era-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        background: white;
        color: var(--era-color, #1e3a5f);
        border: 2px solid var(--era-color, #1e3a5f);
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-radius: 50px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
    }

    .timeline__era-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--era-color, #1e3a5f);
    }

    /* ============ HISTORY MODAL ============ */
    .history-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    .history-modal.is-open {
        opacity: 1;
        visibility: visible;
    }

    .history-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .history-modal__panel {
        position: relative;
        z-index: 1;
        width: min(720px, 100%);
        max-height: 90vh;
        background: white;
        border-radius: 24px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transform: translateY(30px) scale(0.96);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .history-modal.is-open .history-modal__panel {
        transform: translateY(0) scale(1);
    }

    .history-modal__close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.06);
        border: none;
        color: #1e293b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .history-modal__close:hover {
        background: #1e293b;
        color: white;
        transform: rotate(90deg);
    }

    .history-modal__close svg {
        width: 18px;
        height: 18px;
        stroke-linecap: round;
    }

    .history-modal__header {
        position: relative;
        padding: 2rem 2.5rem 1rem;
        background: linear-gradient(135deg,
                color-mix(in srgb, var(--modal-era-color, #1e3a5f) 8%, white) 0%,
                white 100%);
        border-top: 6px solid var(--modal-era-color, #1e3a5f);
    }

    .history-modal__era {
        display: inline-block;
        padding: 0.25rem 0.85rem;
        background: var(--modal-era-color, #1e3a5f);
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 50px;
        letter-spacing: 0.5px;
        margin-bottom: 0.85rem;
    }

    .history-modal__year {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        color: var(--modal-era-color, #1e3a5f);
        margin-bottom: 0.4rem;
    }

    .history-modal__title {
        font-size: 1.65rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        line-height: 1.35;
    }

    .history-modal__subtitle {
        margin: 0.4rem 0 0;
        color: #64748b;
        font-size: 0.95rem;
    }

    .history-modal__divider {
        height: 1px;
        background: rgba(15, 23, 42, 0.08);
        margin: 0 2.5rem;
    }

    .history-modal__body {
        flex: 1;
        overflow-y: auto;
        padding: 1.75rem 2.5rem;
        color: #334155;
        line-height: 1.85;
        font-size: 1rem;
    }

    .history-modal__body p {
        margin: 0 0 1rem;
    }

    .history-modal__body p:last-child {
        margin-bottom: 0;
    }

    .history-modal__body p:first-child::first-letter {
        font-size: 2.6rem;
        font-weight: 800;
        color: var(--modal-era-color, #1e3a5f);
        float: left;
        line-height: 1;
        margin: 0.15rem 0.5rem 0 0;
    }

    .history-modal__footer {
        padding: 1.25rem 2.5rem 1.75rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
        text-align: right;
    }

    /* Body scroll lock when modal open */
    body.has-history-modal {
        overflow: hidden;
    }

    @media (max-width: 640px) {
        .history-modal {
            padding: 0;
            align-items: flex-end;
        }

        .history-modal__panel {
            border-radius: 24px 24px 0 0;
            max-height: 95vh;
            transform: translateY(100%);
        }

        .history-modal.is-open .history-modal__panel {
            transform: translateY(0);
        }

        .history-modal__header {
            padding: 2rem 1.5rem 1rem;
        }

        .history-modal__divider {
            margin: 0 1.5rem;
        }

        .history-modal__body {
            padding: 1.5rem;
            font-size: 0.95rem;
        }

        .history-modal__footer {
            padding: 1rem 1.5rem 1.5rem;
        }

        .history-modal__title {
            font-size: 1.35rem;
        }
    }

    /* ============ CALLOUT ============ */
    .about-callout {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 2.25rem;
        background: linear-gradient(135deg, rgba(30, 58, 95, 0.04), rgba(45, 125, 70, 0.06));
        border-left: 4px solid #2d7d46;
        border-radius: 12px;
    }

    .about-callout__label {
        display: inline-block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #2d7d46;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .about-callout__text {
        margin: 0;
        font-size: 1.1rem;
        line-height: 1.7;
        color: #1e293b;
    }

    /* ============ EXECUTIVE SLIDER (POSTERS) ============ */
    .exec-slider {
        position: relative;
        max-width: 1100px;
        margin: 2rem auto 0;
    }

    .exec-slider__viewport {
        overflow: hidden;
        border-radius: 24px;
        padding: 1.5rem 0.5rem;
    }

    .exec-slider__track {
        display: flex;
        gap: 1.5rem;
        transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
        will-change: transform;
        padding: 0 1rem;
    }

    .exec-poster {
        flex: 0 0 calc((100% - 3rem) / 3);
        min-width: 0;
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        background: #0f172a;
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.12);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
        text-decoration: none;
        color: inherit;
        aspect-ratio: 3 / 4;
        display: block;
    }

    .exec-poster:hover {
        transform: translateY(-8px);
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.22);
    }

    .exec-poster__image {
        position: absolute;
        inset: 0;
        overflow: hidden;
        background: linear-gradient(135deg, #1e293b, #0f172a);
    }

    .exec-poster__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s ease;
        display: block;
    }

    .exec-poster:hover .exec-poster__image img {
        transform: scale(1.06);
    }

    .exec-poster__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.35);
    }

    .exec-poster__placeholder svg {
        width: 64px;
        height: 64px;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .exec-poster__caption {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 1.5rem 1.25rem 1.1rem;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.6) 60%, transparent 100%);
        color: white;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        transform: translateY(0);
        transition: transform 0.35s ease;
    }

    .exec-poster__caption strong {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .exec-poster__caption span {
        font-size: 0.78rem;
        opacity: 0.85;
        font-weight: 400;
    }

    .exec-poster__index {
        position: absolute;
        top: 0.85rem;
        right: 1rem;
        z-index: 2;
        padding: 0.2rem 0.6rem;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        font-size: 0.75rem;
        font-weight: 800;
        color: white;
        letter-spacing: 1.5px;
        border-radius: 50px;
    }

    .exec-poster--linked::after {
        content: '';
        position: absolute;
        top: 0.85rem;
        left: 1rem;
        z-index: 2;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231e3a5f' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M7 17L17 7M17 7H8M17 7v9'/></svg>") center/16px no-repeat;
    }

    /* Empty state */
    .exec-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-radius: 20px;
        border: 2px dashed rgba(15, 23, 42, 0.12);
        margin: 2rem auto 0;
        max-width: 600px;
    }

    .exec-empty svg {
        width: 56px;
        height: 56px;
        color: #94a3b8;
        margin-bottom: 1rem;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .exec-empty h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.5rem;
    }

    .exec-empty p {
        margin: 0;
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Slider nav */
    .exec-slider__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: white;
        border: none;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.18);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3a5f;
        transition: all 0.25s ease;
    }

    .exec-slider__nav:hover {
        background: #1e3a5f;
        color: white;
        transform: translateY(-50%) scale(1.08);
    }

    .exec-slider__nav svg {
        width: 22px;
        height: 22px;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .exec-slider__nav--prev {
        left: -10px;
    }

    .exec-slider__nav--next {
        right: -10px;
    }

    .exec-slider__nav:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .exec-slider__nav:disabled:hover {
        background: white;
        color: #1e3a5f;
        transform: translateY(-50%);
    }

    .exec-slider__dots {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .exec-slider__dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(30, 58, 95, 0.2);
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .exec-slider__dot:hover {
        background: rgba(30, 58, 95, 0.4);
    }

    .exec-slider__dot.is-active {
        width: 28px;
        border-radius: 5px;
        background: linear-gradient(135deg, #1e3a5f, #2d7d46);
    }

    /* ============ LISTS ============ */
    .about-list {
        list-style: none;
        padding: 0;
        margin: 0 auto 1rem;
        max-width: 800px;
    }

    .about-list--mission {
        list-style: decimal;
        padding-left: 1.5rem;
    }

    .about-list--policy {
        list-style: decimal;
        padding-left: 1.5rem;
    }

    .about-list--strategy {
        list-style: disc;
        padding-left: 1.5rem;
    }

    .about-list__item {
        padding: 0.6rem 0;
        line-height: 1.7;
        color: #1e293b;
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1024px) {
        .exec-poster {
            flex: 0 0 calc((100% - 1.5rem) / 2);
        }
    }

    @media (max-width: 768px) {
        .about-tabs__panels {
            padding: 2rem 1.25rem;
        }

        .about-tabs__btn {
            min-width: auto;
            font-size: 0.85rem;
            padding: 0.75rem 0.85rem;
        }

        .about-tabs__btn-text {
            display: none;
        }

        .about-tabs__btn-icon {
            width: 22px;
            height: 22px;
        }

        .about-tabs__btn.is-active {
            background: linear-gradient(135deg, #1e3a5f, #2d7d46);
            color: white;
        }

        .about-tabs__btn.is-active::after {
            display: none;
        }

        /* Timeline → single column on mobile */
        .timeline__line {
            left: 24px;
            transform: none;
        }

        .timeline__item,
        .timeline__item--left,
        .timeline__item--right {
            width: 100%;
            left: 0;
            text-align: left;
            padding-left: 60px;
            padding-right: 1rem;
        }

        .timeline__item--left .timeline__dot,
        .timeline__item--right .timeline__dot {
            left: 13px;
            right: auto;
        }

        .timeline__item--left.timeline__item--highlight .timeline__dot,
        .timeline__item--right.timeline__item--highlight .timeline__dot {
            left: 10px;
            right: auto;
        }

        .timeline__item--left .timeline__card::before,
        .timeline__item--right .timeline__card::before {
            left: -8px;
            right: auto;
            box-shadow: -4px 4px 8px -3px rgba(15, 23, 42, 0.05);
        }

        .timeline__item--left .timeline__card .timeline__star,
        .timeline__item--right .timeline__card .timeline__star {
            left: auto;
            right: 1rem;
        }

        .timeline__era-divider {
            text-align: left;
            padding-left: 60px;
        }

        .exec-poster {
            flex: 0 0 100%;
        }

        .exec-slider__nav--prev {
            left: 4px;
        }

        .exec-slider__nav--next {
            right: 4px;
        }
    }

    @media (max-width: 480px) {
        .about-tabs__nav {
            padding: 0.35rem;
            gap: 0.15rem;
        }

        .about-tabs__btn {
            padding: 0.6rem 0.5rem;
        }
    }
</style>

<script>
    (function() {
        'use strict';

        // ===== TABS =====
        var tabsRoot = document.getElementById('aboutTabs');
        if (tabsRoot) {
            var tabBtns = tabsRoot.querySelectorAll('.about-tabs__btn');
            var tabPanels = tabsRoot.querySelectorAll('.about-tabs__panel');

            function activateTab(name) {
                tabBtns.forEach(function(btn) {
                    var on = btn.getAttribute('data-tab') === name;
                    btn.classList.toggle('is-active', on);
                    btn.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                tabPanels.forEach(function(panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-panel') === name);
                });
                // Re-run timeline calc when history tab is shown
                if (name === 'history') {
                    requestAnimationFrame(updateTimeline);
                }
                // Reset slider position when executives tab opens
                if (name === 'executives') {
                    requestAnimationFrame(function() {
                        goToSlide(currentSlide, false);
                    });
                }
            }

            tabBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    activateTab(btn.getAttribute('data-tab'));
                });
            });

            // Open tab from URL hash (e.g. #executives)
            var hash = (location.hash || '').replace('#', '');
            if (hash && tabsRoot.querySelector('[data-tab="' + hash + '"]')) {
                activateTab(hash);
            }
        }

        // ===== TIMELINE =====
        var timeline = document.getElementById('timeline');
        var fill = document.getElementById('timelineFill');
        var items = timeline ? timeline.querySelectorAll('.timeline__item') : [];

        function updateTimeline() {
            if (!timeline || !fill) return;
            var rect = timeline.getBoundingClientRect();
            var vh = window.innerHeight;
            var trigger = vh * 0.55;

            var total = rect.height;
            var scrolled = Math.max(0, trigger - rect.top);
            var pct = Math.max(0, Math.min(1, scrolled / total));
            fill.style.height = (pct * 100) + '%';

            items.forEach(function(it) {
                var r = it.getBoundingClientRect();
                if (r.top < vh * 0.85 && r.bottom > 0) {
                    it.classList.add('is-visible');
                }
            });
        }

        if (timeline) {
            window.addEventListener('scroll', updateTimeline, {
                passive: true
            });
            window.addEventListener('resize', updateTimeline);
            // Initial
            requestAnimationFrame(updateTimeline);
        }

        // ===== HISTORY MODAL (รวมก้อนเดียว) =====
        var historyModal = document.getElementById('historyModal');
        var lastFocused = null;

        function openHistoryModal() {
            if (!historyModal) return;
            // กำหนด era color เริ่มต้น (น้ำเงิน-เขียว)
            var panel = historyModal.querySelector('.history-modal__panel');
            if (panel) panel.style.setProperty('--modal-era-color', '#1e3a5f');

            var body = document.getElementById('historyModalBody');
            if (body) body.scrollTop = 0;

            lastFocused = document.activeElement;
            historyModal.classList.add('is-open');
            historyModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('has-history-modal');

            setTimeout(function() {
                var closeBtn = historyModal.querySelector('.history-modal__close');
                if (closeBtn) closeBtn.focus();
            }, 50);
        }

        function closeHistoryModal() {
            if (!historyModal) return;
            historyModal.classList.remove('is-open');
            historyModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('has-history-modal');
            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
        }

        if (historyModal) {
            // ปุ่มเปิดทุกตัว (มี 2 ที่: บน-ล่าง timeline)
            document.querySelectorAll('[data-history-open]').forEach(function(btn) {
                btn.addEventListener('click', openHistoryModal);
            });

            // ปุ่มปิด: backdrop / X / ปุ่ม "ปิด"
            historyModal.querySelectorAll('[data-history-close]').forEach(function(el) {
                el.addEventListener('click', closeHistoryModal);
            });

            // ESC ปิด
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && historyModal.classList.contains('is-open')) {
                    closeHistoryModal();
                }
            });

            // Trap Tab ภายใน modal
            historyModal.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab' || !historyModal.classList.contains('is-open')) return;
                var focusables = historyModal.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])');
                if (focusables.length === 0) return;
                var first = focusables[0];
                var last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            });
        }

        // ===== EXECUTIVE SLIDER =====
        var slider = document.getElementById('execSlider');
        var track = document.getElementById('execTrack');
        var viewport = document.getElementById('execViewport');
        var prevBtn = document.getElementById('execPrev');
        var nextBtn = document.getElementById('execNext');
        var dotsWrap = document.getElementById('execDots');
        var currentSlide = 0;

        function getPerView() {
            var w = window.innerWidth;
            if (w <= 768) return 1;
            if (w <= 1024) return 2;
            return 3;
        }

        function getMaxSlide() {
            if (!track) return 0;
            var posters = track.querySelectorAll('.exec-poster');
            return Math.max(0, posters.length - getPerView());
        }

        function goToSlide(idx, animate) {
            if (!track || !viewport) return;
            var max = getMaxSlide();
            if (idx < 0) idx = 0;
            if (idx > max) idx = max;
            currentSlide = idx;

            var posters = track.querySelectorAll('.exec-poster');
            if (posters.length === 0) return;
            var first = posters[0];
            var w = first.getBoundingClientRect().width;
            var gap = parseFloat(getComputedStyle(track).gap) || 0;
            var offset = idx * (w + gap);

            if (animate === false) {
                track.style.transition = 'none';
                track.style.transform = 'translateX(-' + offset + 'px)';
                // Force reflow then restore transition
                void track.offsetWidth;
                track.style.transition = '';
            } else {
                track.style.transform = 'translateX(-' + offset + 'px)';
            }

            // Update dots
            if (dotsWrap) {
                var dots = dotsWrap.querySelectorAll('.exec-slider__dot');
                dots.forEach(function(d, i) {
                    d.classList.toggle('is-active', i === idx);
                });
            }

            // Update nav state
            if (prevBtn) prevBtn.disabled = idx === 0;
            if (nextBtn) nextBtn.disabled = idx >= max;
        }

        if (slider && track) {
            if (prevBtn) prevBtn.addEventListener('click', function() {
                goToSlide(currentSlide - 1, true);
            });
            if (nextBtn) nextBtn.addEventListener('click', function() {
                goToSlide(currentSlide + 1, true);
            });

            if (dotsWrap) {
                dotsWrap.addEventListener('click', function(e) {
                    var t = e.target.closest('.exec-slider__dot');
                    if (!t) return;
                    goToSlide(parseInt(t.getAttribute('data-go'), 10) || 0, true);
                });
            }

            // Touch / swipe
            var startX = 0,
                dx = 0,
                dragging = false;
            viewport.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                dragging = true;
            }, {
                passive: true
            });
            viewport.addEventListener('touchmove', function(e) {
                if (!dragging) return;
                dx = e.touches[0].clientX - startX;
            }, {
                passive: true
            });
            viewport.addEventListener('touchend', function() {
                if (!dragging) return;
                dragging = false;
                if (Math.abs(dx) > 50) {
                    goToSlide(currentSlide + (dx < 0 ? 1 : -1), true);
                }
                dx = 0;
            });

            // Keyboard
            slider.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') goToSlide(currentSlide - 1, true);
                if (e.key === 'ArrowRight') goToSlide(currentSlide + 1, true);
            });

            window.addEventListener('resize', function() {
                goToSlide(currentSlide, false);
            });

            // Initial
            requestAnimationFrame(function() {
                goToSlide(0, false);
            });
        }
    })();
</script>

<?= $this->endSection() ?>