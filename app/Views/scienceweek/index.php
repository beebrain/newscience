<?= $this->extend('scienceweek/layout') ?>
<?= $this->section('content') ?>

<?php
$levelLabels = [
    'primary'         => 'ประถมศึกษา',
    'lower_secondary' => 'ม.ต้น',
    'primary_lower'   => 'ประถม–ม.ต้น',
    'lower_higher'    => 'ม.ต้น–อุดมศึกษา',
    'secondary'       => 'มัธยมศึกษา',
    'higher'          => 'อุดมศึกษา',
    'primary_upper'   => 'ประถมปลาย',
];
?>

<div class="position-relative">
    <!-- Floating Stars -->
    <span class="floating-star star-1">⭐</span>
    <span class="floating-star star-2">✨</span>
    <span class="floating-star star-3">🚀</span>
    <span class="floating-star star-4">🪐</span>

    <!-- Hero Section -->
    <div class="kids-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="d-inline-block px-3 py-1 mb-2 rounded-pill bg-warning text-dark border border-dark fw-bold" style="font-family: var(--sw-kids-heading-font); font-size: 1.1rem;">
                    🌈 งานวิทยาศาสตร์สุดหรรษา 2569 🧪
                </div>
                <h2 class="display-6 fw-bold mb-2 text-primary" style="font-family: var(--sw-kids-heading-font); line-height: 1.2;">
                    ดินแดนแห่งความรู้และจินตนาการ!
                </h2>
                <p class="mb-4" style="font-size: 1.05rem; line-height: 1.6;">
                    ขอเชิญน้อง ๆ และคุณครูมาร่วมสนุกกับกิจกรรมสัปดาห์วิทยาศาสตร์ปีนี้! พบกับรายการนิทรรศการแสนสนุก เเวิร์กชอปทดลองวิทย์ และรายการประกวดแข่งขันชิงรางวัลมากมาย กวาดสายตาดูรายการกิจกรรมทั้งหมดด้านล่างได้เลยครับ 🚀
                </p>
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2">
                    <a href="#activities-list-section" class="btn-kids btn-kids-pink">
                        📋 ดูรายการกิจกรรมทั้งหมด
                    </a>
                    <a href="<?= base_url('scienceweek/verify') ?>" class="btn-kids btn-kids-teal" style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                        🔍 ตรวจสอบรายชื่อผู้สมัคร
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="<?= base_url('assets/images/scienceweek/hero_kids.png') ?>" alt="Science Kids Banner" class="kids-hero-img img-fluid" style="max-height: 250px; object-fit: contain;">
            </div>
        </div>
    </div>

    <!-- Date and Location info banner -->
    <div class="kids-card p-3 mb-4 card-exhibition text-center">
        <div class="row align-items-center g-2">
            <div class="col-md-4">
                <h5 class="mb-0 text-primary" style="font-family: var(--sw-kids-heading-font); font-size: 1.3rem;">📅 วันจัดงาน</h5>
                <p class="mb-0 fw-bold" style="font-size: 0.95rem;">18 - 20 สิงหาคม พ.ศ. 2569</p>
            </div>
            <div class="col-md-4">
                <h5 class="mb-0 text-success" style="font-family: var(--sw-kids-heading-font); font-size: 1.3rem;">📍 สถานที่จัดงาน</h5>
                <p class="mb-0 fw-bold" style="font-size: 0.95rem;">คณะวิทยาศาสตร์และเทคโนโลยี</p>
                <small class="text-muted" style="font-size: 0.8rem;">มหาวิทยาลัยราชภัฏอุตรดิตถ์ วิทยาเขตลำรางทุ่งกะโล่</small>
            </div>
            <div class="col-md-4">
                <h5 class="mb-0 text-danger" style="font-family: var(--sw-kids-heading-font); font-size: 1.3rem;">🔬 ผู้จัดงานหลัก</h5>
                <p class="mb-0 fw-bold" style="font-size: 0.95rem;">มหาวิทยาลัยราชภัฏอุตรดิตถ์ เคียงข้างพัฒนาท้องถิ่น</p>
            </div>
        </div>
    </div>

    <!-- Main Lined Notebook Activities List -->
    <div id="activities-list-section" class="kids-notebook mb-5">
        <!-- Spiral rings look -->
        <div class="kids-notebook-spiral d-none d-md-flex">
            <?php for($i=0; $i<22; $i++): ?>
                <div class="kids-notebook-hole"></div>
            <?php endfor; ?>
        </div>
        
        <h3 class="kids-notebook-title">📝 รายการกิจกรรมในสัปดาห์วิทยาศาสตร์</h3>
        <p class="text-muted mb-4" style="padding-left: 2.2rem; font-size: 0.9rem;">📌 สรุปรายการกิจกรรมทั้งหมดเพื่อความสะดวกในการวางแผนเข้าร่วมและสมัครแข่งขัน</p>
        
        <div class="row g-4">
            
            <!-- Category 1: Exhibitions -->
            <div class="col-12">
                <div class="kids-notebook-category-title" style="color: var(--kids-blue);">
                    🎪 นิทรรศการแสนสนุก
                </div>
                <ul class="kids-notebook-list">
                    <li class="kids-notebook-item" title="นิทรรศการเทิดพระเกียรติ รัชกาลที่ 4">
                        👑 ร.4 พระบิดาแห่งวิทยาศาสตร์ไทย
                    </li>
                    <li class="kids-notebook-item" title="นิทรรศการเทิดพระเกียรติ รัชกาลที่ 9">
                        💡 ร.9 พระบิดาแห่งเทคโนโลยีและนวัตกรรม
                    </li>
                    <li class="kids-notebook-item" title="นิทรรศการเทิดพระเกียรติสมเด็จพระพันปีหลวง">
                        🌿 สมเด็จพระพันปีหลวง: ความหลากหลายชีวภาพ
                    </li>
                    <li class="kids-notebook-item">🪐 เทคโนโลยีทางดาราศาสตร์และอวกาศ</li>
                    <li class="kids-notebook-item">🤖 Smart Living with AI (ชีวิตอัจฉริยะ)</li>
                    <li class="kids-notebook-item">🍃 ชีววิทยาและสิ่งแวดล้อมเพื่ออนาคต</li>
                    <li class="kids-notebook-item">🔢 สนุกคิดคณิตอัจฉริยะ</li>
                    <li class="kids-notebook-item">🧪 วิทยาศาสตร์เคมีอัจฉริยะ สู่อนาคตที่ยั่งยืน</li>
                    <li class="kids-notebook-item">⚡ ฟิสิกส์และเทคโนโลยีกับการเรียนรู้ศตวรรษที่ 21</li>
                    <li class="kids-notebook-item">🏃 วิทยาศาสตร์การกีฬาเพื่อสุขภาพ</li>
                    <li class="kids-notebook-item">🏥 Public Health for Sustainable Community</li>
                    <li class="kids-notebook-item">🎓 นิทรรศการความรู้จากคณะต่าง ๆ</li>
                    <li class="kids-notebook-item">🔬 URU Research Impact: พลังวิจัยสู่ชุมชน</li>
                </ul>
            </div>
            
            <!-- Category 2: Workshops -->
            <div class="col-12">
                <div class="kids-notebook-category-title" style="color: var(--kids-purple);">
                    🧪 เวิร์กชอป DIY & โชว์
                </div>
                <ul class="kids-notebook-list">
                    <li class="kids-notebook-item">🧼 Soap - Sci DIY ทำสบู่แฮนด์เมด</li>
                    <li class="kids-notebook-item">🌶️ Spice & Larb Lab วิทย์รสแซ่บ (ลาบไทย)</li>
                    <li class="kids-notebook-item" style="color: var(--kids-pink); font-weight: bold;">
                        💥 การแสดงทางวิทยาศาสตร์ (Science Show)
                    </li>
                </ul>
            </div>
            
            <!-- Category 3: Competitions -->
            <div class="col-12">
                <div class="kids-notebook-category-title" style="color: var(--kids-orange);">
                    🏆 รายการประกวด & แข่งขัน
                </div>
                <ul class="kids-notebook-list" style="padding-left: 1.5rem;">
                    
                    <!-- Loop Online Competitions from database configuration -->
                    <?php foreach ($competitions as $key => $comp):
                        $totalCap   = $comp['cap_total'];
                        $totalCount = array_sum($caps[$key] ?? []);
                        $isFull     = $totalCap !== null && $totalCount >= $totalCap;
                        $deadlinePassed = $comp['deadline'] !== null && date('Y-m-d') > $comp['deadline'];
                    ?>
                        <li class="kids-notebook-item d-flex justify-content-between align-items-start py-1" style="border-bottom: 1px dashed rgba(0,0,0,0.05);">
                            <span class="pe-2" title="<?= esc($comp['name_th']) ?>">
                                🏆 <?= esc($comp['name_th']) ?>
                            </span>
                            <div class="flex-shrink-0 d-flex gap-1 align-items-center mt-1">
                                <?php foreach ($comp['docs'] ?? [] as $doc): ?>
                                    <a href="<?= base_url(config('SciWeek')->docsPublicPath.'/'.rawurlencode($doc)) ?>" 
                                       target="_blank"
                                       class="badge bg-warning border border-dark text-dark rounded-pill px-2 py-1 text-decoration-none hover-scale" 
                                       style="font-size: 0.75rem; font-family: var(--sw-kids-heading-font); box-shadow: 1px 1px 0px rgba(0,0,0,0.2); transition: transform 0.1s;">
                                        กติกา 📄
                                    </a>
                                <?php endforeach; ?>
                                <?php if ($isFull): ?>
                                    <span class="badge bg-danger border border-dark text-white rounded-pill px-2 py-1" style="font-size: 0.7rem; font-family: var(--sw-kids-heading-font);">เต็มแล้ว</span>
                                <?php elseif ($deadlinePassed): ?>
                                    <span class="badge bg-secondary border border-dark text-white rounded-pill px-2 py-1" style="font-size: 0.7rem; font-family: var(--sw-kids-heading-font);">ปิดรับ</span>
                                <?php else: ?>
                                    <a href="<?= base_url('scienceweek/register/'.$key) ?>" 
                                       class="badge bg-success border border-dark text-white rounded-pill px-2 py-1 text-decoration-none hover-scale" 
                                       style="font-size: 0.75rem; font-family: var(--sw-kids-heading-font); box-shadow: 1px 1px 0px rgba(0,0,0,0.2); transition: transform 0.1s;">
                                        สมัคร 📝
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>

                    <!-- Offline Competitions -->
                    <li class="kids-notebook-item d-flex justify-content-between align-items-start py-1" style="border-bottom: 1px dashed rgba(0,0,0,0.05);">
                        <span class="pe-2">🏆 การประกวดโครงงานวิทยาศาสตร์</span>
                        <div class="flex-shrink-0 d-flex gap-1 align-items-center mt-1">
                            <a href="<?= base_url(config('SciWeek')->docsPublicPath.'/'.rawurlencode('ประกวดโครงงาน.pdf')) ?>" 
                               target="_blank"
                               class="badge bg-warning border border-dark text-dark rounded-pill px-2 py-1 text-decoration-none hover-scale" 
                               style="font-size: 0.75rem; font-family: var(--sw-kids-heading-font); box-shadow: 1px 1px 0px rgba(0,0,0,0.2); transition: transform 0.1s;">
                                กติกา 📄
                            </a>
                            <span class="badge bg-info border border-dark text-dark rounded-pill px-2 py-1" style="font-size: 0.7rem; font-family: var(--sw-kids-heading-font);">ส่งเอกสาร</span>
                        </div>
                    </li>
                    <li class="kids-notebook-item d-flex justify-content-between align-items-start py-1" style="border-bottom: 1px dashed rgba(0,0,0,0.05);">
                        <span class="pe-2">🏆 การประกวดจัดสวนขวด Terrarium</span>
                        <div class="flex-shrink-0 d-flex gap-1 align-items-center mt-1">
                            <a href="<?= base_url(config('SciWeek')->docsPublicPath.'/'.rawurlencode('ประกวดสวนในภาชนะปิด.pdf')) ?>" 
                               target="_blank"
                               class="badge bg-warning border border-dark text-dark rounded-pill px-2 py-1 text-decoration-none hover-scale" 
                               style="font-size: 0.75rem; font-family: var(--sw-kids-heading-font); box-shadow: 1px 1px 0px rgba(0,0,0,0.2); transition: transform 0.1s;">
                                กติกา 📄
                            </a>
                            <span class="badge bg-info border border-dark text-dark rounded-pill px-2 py-1" style="font-size: 0.7rem; font-family: var(--sw-kids-heading-font);">ส่งเอกสาร</span>
                        </div>
                    </li>
                    <li class="kids-notebook-item d-flex justify-content-between align-items-start py-1">
                        <span class="pe-2">🏆 โครงงานสิ่งแวดล้อม GLOBE</span>
                        <span class="badge bg-info border border-dark text-dark rounded-pill px-2 py-1 flex-shrink-0 mt-1" style="font-size: 0.7rem; font-family: var(--sw-kids-heading-font);">ส่งเอกสาร</span>
                    </li>

                </ul>
            </div>

        </div>
    </div>
</div>

<style>
.hover-scale:hover {
    transform: scale(1.1);
}
</style>

<?= $this->endSection() ?>
