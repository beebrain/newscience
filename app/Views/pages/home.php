<?= $this->extend($layout) ?>

<?php
// Helper function to get program icon
function getProgramIcon($programName)
{
    $name = mb_strtolower($programName);

    // Map program names to icons
    if (strpos($name, 'คณิต') !== false || strpos($name, 'math') !== false) {
        // Mathematics icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M8 8h8M8 12h8M8 16h8"/></svg>';
    } elseif (strpos($name, 'เทคโนโลยีสารสนเทศ') !== false || strpos($name, 'information technology') !== false || strpos($name, 'it') !== false) {
        // IT icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
    } elseif (strpos($name, 'คอมพิวเตอร์') !== false || strpos($name, 'computer') !== false) {
        // Computer Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="12" rx="2" ry="2"/><line x1="6" y1="8" x2="6.01" y2="8"/><line x1="10" y1="8" x2="10.01" y2="8"/><line x1="14" y1="8" x2="14.01" y2="8"/><line x1="18" y1="8" x2="18.01" y2="8"/></svg>';
    } elseif (strpos($name, 'ชีว') !== false || strpos($name, 'biology') !== false || strpos($name, 'bio') !== false) {
        // Biology icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>';
    } elseif (strpos($name, 'เคมี') !== false || strpos($name, 'chemistry') !== false || strpos($name, 'chem') !== false) {
        // Chemistry icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6M15 2v6M12 17v5M5 8h14M6 12h12M7 16h10"/><circle cx="12" cy="12" r="3"/></svg>';
    } elseif (strpos($name, 'สิ่งแวดล้อม') !== false || strpos($name, 'environment') !== false) {
        // Environmental Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    } elseif (strpos($name, 'ข้อมูล') !== false || strpos($name, 'data') !== false) {
        // Data Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>';
    } elseif (strpos($name, 'กีฬา') !== false || strpos($name, 'sport') !== false || strpos($name, 'exercise') !== false) {
        // Sports Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>';
    } elseif (strpos($name, 'ประยุกต์') !== false || strpos($name, 'applied') !== false) {
        // Applied Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>';
    } elseif (strpos($name, 'วิศวกรรม') !== false || strpos($name, 'engineering') !== false) {
        // Engineering icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/></svg>';
    } elseif (strpos($name, 'ปัญญา') !== false || strpos($name, 'artificial') !== false || strpos($name, 'ai') !== false) {
        // AI icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><line x1="12" y1="2" x2="12" y2="22"/></svg>';
    } else {
        // Default icon (graduation cap)
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>';
    }
}

// Carousel card image: prefer program hero_image, else program image or name-based fallback
function getProgramCarouselImageUrl(array $program): string
{
    $hero = trim($program['hero_image'] ?? '');
    if ($hero !== '') {
        return base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $hero), '/'));
    }
    $programImage = $program['image'] ?? '';
    if ($programImage !== '' && strpos($programImage, 'http') === 0) {
        return $programImage;
    }
    if ($programImage !== '') {
        return base_url(ltrim($programImage, '/'));
    }
    $name = $program['name_th'] ?? $program['name_en'] ?? '';
    $programImageMap = [
        'ชีววิทยา' => 'biology.png', 'ชีว' => 'biology.png',
        'เคมี' => 'วิทยาศาสตรบัณฑิต สาขาวิชาเคมี.jpg',
        'คณิตศาสตร์' => 'วิทยาศาสตรบัณฑิต สาขาวิชาคณิตศาสตร์ประยุกต์.jpg',
        'คอมพิวเตอร์' => 'วิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการคอมพิวเตอร์.jpg',
        'เทคโนโลยีสารสนเทศ' => 'วิทยาศาสตรบัณฑิต สาขาวิชาเทคโนโลยีสารสนเทศ.jpg',
        'วิทยาการข้อมูล' => 'วิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการข้อมูล.jpg', 'ข้อมูล' => 'ai_data_science.png',
        'สิ่งแวดล้อม' => 'environmental_science.png', 'กีฬา' => 'sports_science.png',
        'สาธารณสุข' => 'สาธารณสุขศาสตรบัณฑิต สาขาวิชาสาธารณสุขศาสตร์.jpg',
        'อาหาร' => 'วิทยาศาสตรบัณฑิต สาขาวิชาอาหารและโภชนาการ.jpg', 'โภชนาการ' => 'วิทยาศาสตรบัณฑิต สาขาวิชาอาหารและโภชนาการ.jpg',
        'ปัญญาประดิษฐ์' => 'ai_data_science.png',
    ];
    foreach ($programImageMap as $keyword => $filename) {
        if (mb_strpos($name, $keyword) !== false) {
            return base_url('assets/images/programs/' . $filename);
        }
    }
    return base_url('assets/images/programs/biology.png');
}
?>

<?= $this->section('content') ?>

<!-- Mourning Popup Overlay -->
<div id="mourning-popup-overlay" class="mourning-popup-overlay" role="dialog" aria-label="แสดงความอาลัย" aria-modal="true" hidden>
    <div class="mourning-popup-backdrop"></div>
    <div class="mourning-popup-wrap">
        <div class="mourning-popup-content">
            <div class="mourning-portrait-container">
                <img src="<?= base_url('assets/images/royal_mourning_princess.jpg') ?>" alt="สมเด็จพระเจ้าลูกเธอ เจ้าฟ้าพัชรกิติยาภา นเรนทิราเทพยวดี กรมหลวงราชสาริณีสิริพัชร มหาวัชรราชธิดา" class="mourning-portrait">
            </div>
            <div class="mourning-text-container">
                <p class="mourning-lead">เสด็จสู่สวรรคาลัย</p>
                <p class="mourning-title">น้อมสำนึกในพระมหากรุณาธิคุณของ</p>
                <h2 class="mourning-royal-name">สมเด็จพระเจ้าลูกเธอ เจ้าฟ้าพัชรกิติยาภา นเรนทิราเทพยวดี<br>กรมหลวงราชสาริณีสิริพัชร มหาวัชรราชธิดา</h2>
                
                <hr class="mourning-divider">
                
                <p class="mourning-title-en">In Reverent Remembrance of Her Royal Highness</p>
                <h3 class="mourning-royal-name-en">Princess Bajrakitiyabha Narendiradebyavati<br>Kromluang Rajasarini Siribajra Mahavajrarajadhita</h3>
                
                <p class="mourning-signature">ข้าพระพุทธเจ้า คณะผู้บริหาร คณาจารย์ เจ้าหน้าที่ และนักศึกษา<br>คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์</p>
            </div>
            <button type="button" class="mourning-enter-btn" id="mourning-enter-btn">เข้าสู่เว็บไซต์</button>
        </div>
    </div>
</div>

<style>
/* ===== Mourning Popup Styles ===== */
.mourning-popup-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease, visibility 0.4s;
    background: rgba(10, 10, 10, 0.98);
}
.mourning-popup-overlay[data-open="true"] {
    opacity: 1;
    visibility: visible;
}
.mourning-popup-wrap {
    position: relative;
    width: 100%;
    max-width: 680px;
    opacity: 0;
    transform: scale(0.92) translateY(12px);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s;
}
.mourning-popup-overlay[data-open="true"] .mourning-popup-wrap {
    opacity: 1;
    transform: scale(1) translateY(0);
}
.mourning-popup-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
.mourning-popup-content {
    background: #151515;
    border: 3px double #d4af37;
    border-radius: 16px;
    padding: 2rem 1.5rem;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.9);
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
    text-align: center;
    color: #e0e0e0;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
    -webkit-overflow-scrolling: touch;
}
.mourning-popup-content::-webkit-scrollbar {
    display: none; /* Hide scrollbar for Chrome, Safari and Opera */
}
.mourning-portrait-container {
    margin-bottom: 1rem;
    display: flex;
    justify-content: center;
}
.mourning-portrait {
    width: 140px;
    height: auto;
    border-radius: 8px;
    border: 2px solid #d4af37;
    box-shadow: 0 8px 24px rgba(0,0,0,0.6);
    object-fit: cover;
}
.mourning-text-container {
    font-family: 'Sarabun', 'Noto Sans Thai', sans-serif;
}
.mourning-lead {
    font-size: 1.45rem;
    color: #d4af37;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}
.mourning-title {
    font-size: 1.15rem;
    color: #b0b0b0;
    font-weight: 600;
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
}
.mourning-subtitle {
    font-size: 1.05rem;
    color: #a0a0a0;
    margin-bottom: 1.5rem;
}
.mourning-royal-name {
    font-size: 1.3rem;
    color: #ffffff;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 1rem;
}
.mourning-divider {
    border: 0;
    height: 1px;
    background: linear-gradient(to right, transparent, #d4af37, transparent);
    margin: 1rem auto;
    width: 60%;
}
.mourning-title-en {
    font-size: 1.1rem;
    color: #d4af37;
    margin-bottom: 0.5rem;
    font-style: italic;
}
.mourning-royal-name-en {
    font-size: 1.15rem;
    color: #ffffff;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 1.25rem;
}
.mourning-details {
    font-size: 1.1rem;
    color: #cccccc;
    line-height: 1.6;
    margin-bottom: 1.25rem;
}
.mourning-signature {
    font-size: 0.95rem;
    color: #888888;
    line-height: 1.7;
    margin-bottom: 1.5rem;
}
.mourning-enter-btn {
    padding: 0.85rem 3.5rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: #111111;
    background: #d4af37;
    border: 1px solid #d4af37;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25);
}
.mourning-enter-btn:hover {
    background: transparent;
    color: #d4af37;
    border-color: #d4af37;
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
}

@media (max-width: 640px) {
    .mourning-popup-content {
        padding: 1.25rem 0.75rem;
    }
    .mourning-portrait-container {
        margin-bottom: 0.75rem;
    }
    .mourning-portrait {
        width: 100px;
    }
    .mourning-royal-name {
        font-size: 1.15rem;
        margin-bottom: 0.75rem;
    }
    .mourning-royal-name-en {
        font-size: 1.05rem;
        margin-bottom: 1rem;
    }
    .mourning-lead {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    .mourning-title {
        font-size: 1.0rem;
        margin-bottom: 0.75rem;
    }
    .mourning-divider {
        margin: 1rem auto;
    }
    .mourning-signature {
        font-size: 0.85rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }
    .mourning-enter-btn {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
}
</style>

<?php
// ประกาศด่วน (ป๊อปอัป) — แสดงเฉพาะเมื่อมีข้อมูลและอยู่หน้าแรก
$urgentPopups = $urgent_popups ?? [];
?>

<?php
// แสดงเฉพาะประกาศที่มีรูป (แบบรูปอย่างเดียว)
$urgentPopupsWithImage = array_values(array_filter($urgentPopups, function ($p) { return !empty($p['image_url']); }));
?>
<?php if (!empty($urgentPopupsWithImage)): ?>
<!-- Urgent Popup: แบบรูปอย่างเดียว, สไตล์ Modern -->
<div id="urgent-popup-overlay" class="urgent-popup-overlay" role="dialog" aria-label="ประกาศด่วน" aria-modal="true" hidden>
    <div class="urgent-popup-backdrop"></div>
    <div class="urgent-popup-wrap">
        <div class="urgent-popup-box urgent-popup-box--image-only">
            <button type="button" class="urgent-popup-close" id="urgent-popup-close" aria-label="ปิด">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php if (count($urgentPopupsWithImage) > 1): ?>
            <button type="button" class="urgent-popup-arrow urgent-popup-prev" aria-label="ข่าวก่อนหน้า">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button type="button" class="urgent-popup-arrow urgent-popup-next" aria-label="ข่าวถัดไป">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <?php endif; ?>
            <div class="urgent-popup-carousel">
                <?php foreach ($urgentPopupsWithImage as $idx => $p): ?>
                    <?php
                    $pw = (int)($p['image_width']  ?? 0);
                    $ph = (int)($p['image_height'] ?? 0);
                    $hasDims = $pw > 0 && $ph > 0;
                    $aspectStyle = $hasDims ? 'style="--media-aspect:' . $pw . '/' . $ph . ';"' : '';
                    $imgUrl = esc($p['image_url']);
                    $imgAlt = esc($p['title'] ?? '');
                    ?>
                <div class="urgent-popup-slide <?= $idx === 0 ? 'active' : '' ?>" data-popup-id="<?= (int)($p['id'] ?? 0) ?>">
                    <?php if (!empty($p['link_url'])): ?>
                    <a href="<?= esc($p['link_url']) ?>" class="smart-media-frame smart-media-frame--popup" <?= $aspectStyle ?> target="_blank" rel="noopener noreferrer">
                        <?php if ($hasDims): ?>
                        <img class="smart-media-frame__backdrop" src="<?= $imgUrl ?>" alt="" aria-hidden="true" loading="lazy">
                        <?php endif; ?>
                        <img class="smart-media-frame__image" src="<?= $imgUrl ?>" alt="<?= $imgAlt ?>">
                    </a>
                    <?php else: ?>
                    <div class="smart-media-frame smart-media-frame--popup" <?= $aspectStyle ?>>
                        <?php if ($hasDims): ?>
                        <img class="smart-media-frame__backdrop" src="<?= $imgUrl ?>" alt="" aria-hidden="true" loading="lazy">
                        <?php endif; ?>
                        <img class="smart-media-frame__image" src="<?= $imgUrl ?>" alt="<?= $imgAlt ?>">
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($urgentPopupsWithImage) > 1): ?>
            <div class="urgent-popup-dots">
                <?php foreach ($urgentPopupsWithImage as $idx => $p): ?>
                <button type="button" class="urgent-popup-dot <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>" aria-label="ข่าวที่ <?= $idx + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="urgent-popup-footer">
                <label class="urgent-popup-dismiss-label">
                    <input type="checkbox" id="urgent-popup-dismiss-checkbox" value="1"> ไม่แสดงอีก
                </label>
                <button type="button" class="urgent-popup-btn-close" id="urgent-popup-ok">ปิด</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== Urgent Popup — Modern style ===== */
.urgent-popup-overlay {
    position: fixed; inset: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    padding: 1.25rem;
    opacity: 0; visibility: hidden;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s;
}
.urgent-popup-overlay[data-open="true"] {
    opacity: 1; visibility: visible;
}
.urgent-popup-overlay[data-open="true"] .urgent-popup-wrap {
    animation: urgentPopupIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
@keyframes urgentPopupIn {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.urgent-popup-backdrop {
    position: absolute; inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.urgent-popup-wrap {
    position: relative;
    width: 100%; max-width: 580px; max-height: 90vh;
    opacity: 0;
}
.urgent-popup-box--image-only {
    position: relative;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.05);
    overflow: hidden;
    display: flex; flex-direction: column;
    max-height: 90vh;
}
.urgent-popup-close {
    position: absolute; top: 1rem; right: 1rem; z-index: 3;
    width: 44px; height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.95);
    color: #475569;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.12);
    transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
}
.urgent-popup-close:hover {
    background: #fff;
    color: #1e293b;
    transform: scale(1.08);
}
.urgent-popup-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    z-index: 2;
    width: 48px; height: 48px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.95);
    color: #1e293b;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}
.urgent-popup-arrow:hover {
    background: #fff;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
    transform: translateY(-50%) scale(1.08);
}
.urgent-popup-prev { left: 1rem; }
.urgent-popup-next { right: 1rem; }
.urgent-popup-carousel {
    position: relative;
    width: 100%;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    transition: min-height 0.3s ease;
}
.urgent-popup-slide { display: none; }
.urgent-popup-slide.active { display: block; }
.urgent-popup-slide .smart-media-frame {
    --media-max-width: 100%;
    border-radius: 0;
    min-height: 280px;
}
/* Fallback สำหรับรูปเก่าที่ไม่มี dimensions — carousel ใช้ aspect 4:3 */
.urgent-popup-slide .smart-media-frame:not([style*="--media-aspect"]) {
    aspect-ratio: 4/3;
}
.urgent-popup-dots {
    display: flex; justify-content: center; align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    flex-shrink: 0;
}
.urgent-popup-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    border: none;
    background: #e2e8f0;
    cursor: pointer;
    padding: 0;
    transition: background 0.25s ease, transform 0.2s ease;
}
.urgent-popup-dot:hover { background: #cbd5e1; }
.urgent-popup-dot.active {
    background: var(--primary, #eab308);
    transform: scale(1.2);
    box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.3);
}
.urgent-popup-footer {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 0.75rem;
    padding: 1rem 1.25rem 1.25rem;
    background: #fafafa;
    border-top: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.urgent-popup-dismiss-label {
    font-size: 0.875rem;
    color: #64748b;
    cursor: pointer;
    display: flex; align-items: center; gap: 0.5rem;
    margin: 0;
    user-select: none;
}
.urgent-popup-dismiss-label input { accent-color: var(--primary, #eab308); }
.urgent-popup-btn-close {
    padding: 0.5rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 500;
    border: none;
    border-radius: 10px;
    background: var(--primary, #eab308);
    color: #1e293b;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.05s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 8px rgba(234, 179, 8, 0.35);
}
.urgent-popup-btn-close:hover {
    background: var(--primary-dark, #ca8a04);
    box-shadow: 0 4px 12px rgba(234, 179, 8, 0.4);
}
.urgent-popup-btn-close:active { transform: scale(0.98); }
@media (max-width: 640px) {
    .urgent-popup-box--image-only { border-radius: 16px; }
    .urgent-popup-close { top: 0.75rem; right: 0.75rem; width: 40px; height: 40px; }
    .urgent-popup-arrow { width: 42px; height: 42px; }
    .urgent-popup-prev { left: 0.5rem; }
    .urgent-popup-next { right: 0.5rem; }
</style>
<?php endif; ?>

<script>
(function() {
    var mourningOverlay = document.getElementById('mourning-popup-overlay');
    var urgentOverlay = document.getElementById('urgent-popup-overlay');

    // Setup urgent popup logic but do NOT open it yet
    var urgentSlides = urgentOverlay ? urgentOverlay.querySelectorAll('.urgent-popup-slide') : [];
    var urgentPopupIds = [];
    urgentSlides.forEach(function(s) { var id = s.dataset.popupId; if (id) urgentPopupIds.push(id); });
    var urgentDismissedKey = 'urgent_popup_dismissed';
    function getUrgentDismissed() { try { var j = localStorage.getItem(urgentDismissedKey); return j ? JSON.parse(j) : []; } catch (e) { return []; } }
    function setUrgentDismissed(id) { var a = getUrgentDismissed(); if (a.indexOf(id) === -1) a.push(id); localStorage.setItem(urgentDismissedKey, JSON.stringify(a)); }
    var visibleUrgentIds = urgentPopupIds.filter(function(id) { return getUrgentDismissed().indexOf(id) === -1; });

    var currentUrgentIndex = 0;
    var currentUrgentSlide = function() { return urgentSlides[currentUrgentIndex]; };
    var urgentAutoplayMs = 3000;
    var urgentAutoplayTimer = null;
    function stopUrgentAutoplay() {
        if (urgentAutoplayTimer) {
            clearInterval(urgentAutoplayTimer);
            urgentAutoplayTimer = null;
        }
    }
    function startUrgentAutoplay() {
        if (urgentSlides.length <= 1) return;
        stopUrgentAutoplay();
        urgentAutoplayTimer = setInterval(function() {
            showUrgentSlide(currentUrgentIndex + 1, true);
        }, urgentAutoplayMs);
    }
    function showUrgentSlide(i, fromAutoplay) {
        if (i < 0) i = urgentSlides.length - 1;
        if (i >= urgentSlides.length) i = 0;
        currentUrgentIndex = i;
        urgentSlides.forEach(function(s, idx) { s.classList.toggle('active', idx === currentUrgentIndex); });
        if (urgentOverlay) {
            urgentOverlay.querySelectorAll('.urgent-popup-dot').forEach(function(d, idx) { d.classList.toggle('active', idx === currentUrgentIndex); });
        }
        if (!fromAutoplay) startUrgentAutoplay();
    }

    function closeUrgentPopup() {
        stopUrgentAutoplay();
        if (urgentOverlay) {
            var cb = urgentOverlay.querySelector('#urgent-popup-dismiss-checkbox');
            if (cb && cb.checked && currentUrgentSlide()) {
                var id = currentUrgentSlide().dataset.popupId;
                if (id) setUrgentDismissed(id);
            }
            urgentOverlay.setAttribute('data-open', 'false');
            urgentOverlay.setAttribute('hidden', '');
        }
        document.body.style.overflow = '';
    }

    if (urgentOverlay && urgentSlides.length > 0) {
        var backdrop = urgentOverlay.querySelector('.urgent-popup-backdrop');
        var closeBtn = urgentOverlay.querySelector('#urgent-popup-close');
        var okBtn = urgentOverlay.querySelector('#urgent-popup-ok');
        if (backdrop) backdrop.addEventListener('click', closeUrgentPopup);
        if (closeBtn) closeBtn.addEventListener('click', closeUrgentPopup);
        if (okBtn) okBtn.addEventListener('click', closeUrgentPopup);

        var nextBtn = urgentOverlay.querySelector('.urgent-popup-next');
        var prevBtn = urgentOverlay.querySelector('.urgent-popup-prev');
        if (nextBtn) nextBtn.addEventListener('click', function(e) { e.preventDefault(); showUrgentSlide(currentUrgentIndex + 1); });
        if (prevBtn) prevBtn.addEventListener('click', function(e) { e.preventDefault(); showUrgentSlide(currentUrgentIndex - 1); });
        urgentOverlay.querySelectorAll('.urgent-popup-dot').forEach(function(dot) {
            dot.addEventListener('click', function() { showUrgentSlide(parseInt(this.dataset.index, 10)); });
        });
        var carousel = urgentOverlay.querySelector('.urgent-popup-carousel');
        if (carousel && urgentSlides.length > 1) {
            var touchStartX = 0, touchEndX = 0;
            carousel.addEventListener('touchstart', function(e) { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
            carousel.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                var d = touchStartX - touchEndX;
                if (Math.abs(d) > 50) { showUrgentSlide(d > 0 ? currentUrgentIndex + 1 : currentUrgentIndex - 1); }
            }, { passive: true });
        }
    }

    // Function to check and show urgent popup
    function tryShowUrgentPopup() {
        if (urgentOverlay && visibleUrgentIds.length > 0) {
            urgentOverlay.removeAttribute('hidden');
            urgentOverlay.setAttribute('data-open', 'true');
            document.body.style.overflow = 'hidden';
            startUrgentAutoplay();
        } else {
            document.body.style.overflow = '';
        }
    }

    // Mourning popup actions
    if (mourningOverlay) {
        var enterBtn = mourningOverlay.querySelector('#mourning-enter-btn');
        if (enterBtn) {
            enterBtn.addEventListener('click', function() {
                mourningOverlay.setAttribute('data-open', 'false');
                mourningOverlay.setAttribute('hidden', '');
                // Try opening the next popup (urgent popup)
                tryShowUrgentPopup();
            });
        }
        // Force open mourning popup
        mourningOverlay.removeAttribute('hidden');
        mourningOverlay.setAttribute('data-open', 'true');
        document.body.style.overflow = 'hidden';
    } else {
        // If no mourning popup, try showing urgent popup directly
        tryShowUrgentPopup();
    }
})();
</script>

<?php
if (!function_exists('getShortDegree')) {
    function getShortDegree($degree) {
        $degree = trim($degree);
        if (preg_match('/\(([^)]+)\)/', $degree, $matches)) {
            return $matches[1];
        }
        return $degree;
    }
}

// Get settings with defaults
$siteName = $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี';
$siteNameEn = $settings['site_name_en'] ?? 'Faculty of Science and Technology';
$university = $settings['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์';
$universityEn = $settings['university_name_en'] ?? 'Uttaradit Rajabhat University';
$heroTitle = $settings['hero_title_th'] ?? 'ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี';
$heroSubtitle = $settings['hero_subtitle_th'] ?? $university;
$heroDesc = $settings['hero_description_th'] ?? 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น';
?>

<!-- Hero Carousel Section (ข้อมูลจากฐานข้อมูล: hero_slides + site_settings) -->
<section class="hero-carousel">
    <?php
    // Hero slides จากฐานข้อมูล (จัดการที่ Admin > Hero Slides)
    $heroSlides = $hero_slides ?? [];

    // ถ้าไม่มีสไลด์จาก DB ใช้สไลด์เดียวจาก site_settings (ฐานข้อมูล)
    if (empty($heroSlides)) {
        $heroSlides = [[
            'image' => !empty($settings['hero_image']) ? (strpos($settings['hero_image'], 'http') === 0 ? $settings['hero_image'] : base_url($settings['hero_image'])) : base_url('assets/images/hero_background.png'),
            'title' => $heroTitle,
            'subtitle' => $universityEn,
            'description' => $heroDesc,
            'show_buttons' => true,
            'link' => '',
            'link_text' => 'ดูรายละเอียด',
        ]];
    }
    ?>

    <div class="hero-carousel__container" id="heroCarousel">
        <div class="hero-carousel__slides">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <div class="hero-carousel__slide <?= $index === 0 ? 'active' : '' ?>"
                    style="background-image: url('<?= esc($slide['image'] ?? '') ?>');">
                    <div class="hero-carousel__overlay"></div>
                    <div class="container">
                        <div class="hero__content">
                            <?php if (!empty($slide['subtitle'])): ?>
                                <span class="hero__subtitle"><?= esc($slide['subtitle']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($slide['title'])): ?>
                                <h1 class="hero__title"><?= esc($slide['title']) ?></h1>
                            <?php endif; ?>
                            <?php if (!empty($slide['description'])): ?>
                                <p class="hero__description"><?= esc($slide['description']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($slide['show_buttons'])): ?>
                                <div class="hero__actions">
                                    <a href="<?= base_url('about') ?>" class="btn btn-primary btn-lg">เกี่ยวกับเรา</a>
                                    <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-lg">สมัครเรียน</a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($slide['link'])): ?>
                                <div class="hero__actions">
                                    <a href="<?= esc($slide['link']) ?>" class="btn btn-primary btn-lg">ดูรายละเอียด</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($heroSlides) > 1): ?>
            <!-- Navigation Arrows -->
            <button class="hero-carousel__nav hero-carousel__nav--prev" onclick="heroCarouselNav(-1)" aria-label="Previous slide">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="hero-carousel__nav hero-carousel__nav--next" onclick="heroCarouselNav(1)" aria-label="Next slide">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <!-- Dots Indicator -->
            <div class="hero-carousel__dots">
                <?php foreach ($heroSlides as $index => $slide): ?>
                    <button class="hero-carousel__dot <?= $index === 0 ? 'active' : '' ?>"
                        onclick="heroCarouselGoto(<?= $index ?>)"
                        aria-label="Go to slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>



<!-- Admission CTA Section (เน้นการรับสมัครก่อน) -->
<section class="cta-section section-dark-blue">
    <div class="container">
        <h2 class="cta-section__title">เริ่มต้นเส้นทางของคุณ</h2>
        <p class="cta-section__description">
            สำรวจความเป็นไปได้ของการศึกษาที่คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์
        </p>
        <div class="cta-section__actions flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-3 sm:gap-4 w-full max-w-md sm:max-w-none mx-auto px-1">
            <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-lg w-full sm:w-auto text-center">สมัครเรียน</a>
            <a href="<?= base_url('contact') ?>" class="btn btn-outline btn-lg w-full sm:w-auto text-center">ติดต่อเรา</a>
        </div>
    </div>
</section>

<!-- Programs Section (แนะนำหลักสูตรแต่ละหลักสูตร) -->
<section class="section section-light home-programs-section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">หลักสูตร</span>
            <h2 class="section-header__title">หลักสูตรที่เปิดสอน</h2>
            <p class="section-header__description">
                มุ่งสร้างบัณฑิตคุณภาพ พัฒนาองค์ความรู้และเทคโนโลยี นวัตกรรมสู่สังคม
            </p>
        </div>

        <div class="programs-modern-grid">
            <!-- ระดับปริญญาตรี -->
            <?php if (!empty($bachelor_programs)): ?>
                <?php foreach ($bachelor_programs as $program): ?>
                    <?php $programLink = base_url('program/' . (int)($program['id'] ?? 0)); ?>
                    <div class="program-modern-card animate-on-scroll">
                        <div class="program-modern-card__image-wrapper">
                            <?php $programImage = getProgramCarouselImageUrl($program); ?>
                            <img src="<?= esc($programImage) ?>"
                                alt="<?= esc($program['name_th']) ?>"
                                class="program-modern-card__image"
                                loading="lazy"
                                onerror="this.src='<?= base_url('assets/images/placeholder.png') ?>'">
                            <span class="program-modern-card__badge-floating">
                                <?= esc(getShortDegree($program['degree_th'] ?? 'วท.บ.')) ?>
                            </span>
                        </div>
                        <div class="program-modern-card__content">
                            <span class="program-modern-card__degree-label"><?= esc($program['degree_th'] ?? '') ?></span>
                            <h4 class="program-modern-card__title"><?= esc($program['name_th']) ?></h4>
                            <?php if (!empty($program['description'])): ?>
                                <p class="program-modern-card__description">
                                    <?= esc(mb_substr(strip_tags($program['description']), 0, 95)) ?>...
                                </p>
                            <?php endif; ?>
                            <a href="<?= esc($programLink) ?>" class="program-modern-card__link" aria-label="ดูข้อมูลหลักสูตร <?= esc($program['name_th']) ?>">
                                <span>ดูรายละเอียดหลักสูตร</span>
                                <svg class="arrow-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ระดับบัณฑิตศึกษา (ปริญญาโท) -->
            <?php if (!empty($master_programs)): ?>
                <?php foreach ($master_programs as $program): ?>
                    <?php $programLink = base_url('program/' . (int)($program['id'] ?? 0)); ?>
                    <div class="program-modern-card program-modern-card--master animate-on-scroll">
                        <div class="program-modern-card__image-wrapper">
                            <?php $programImage = getProgramCarouselImageUrl($program); ?>
                            <img src="<?= esc($programImage) ?>"
                                alt="<?= esc($program['name_th']) ?>"
                                class="program-modern-card__image"
                                loading="lazy"
                                onerror="this.src='<?= base_url('assets/images/placeholder.png') ?>'">
                            <span class="program-modern-card__badge-floating program-modern-card__badge-floating--master">
                                <?= esc(getShortDegree($program['degree_th'] ?? 'วท.ม.')) ?>
                            </span>
                        </div>
                        <div class="program-modern-card__content">
                            <span class="program-modern-card__degree-label"><?= esc($program['degree_th'] ?? '') ?></span>
                            <h4 class="program-modern-card__title"><?= esc($program['name_th']) ?></h4>
                            <?php if (!empty($program['description'])): ?>
                                <p class="program-modern-card__description">
                                    <?= esc(mb_substr(strip_tags($program['description']), 0, 95)) ?>...
                                </p>
                            <?php endif; ?>
                            <a href="<?= esc($programLink) ?>" class="program-modern-card__link" aria-label="ดูข้อมูลหลักสูตร <?= esc($program['name_th']) ?>">
                                <span>ดูรายละเอียดหลักสูตร</span>
                                <svg class="arrow-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ระดับบัณฑิตศึกษา (ปริญญาเอก) -->
            <?php if (!empty($doctorate_programs)): ?>
                <?php foreach ($doctorate_programs as $program): ?>
                    <?php $programLink = base_url('program/' . (int)($program['id'] ?? 0)); ?>
                    <div class="program-modern-card program-modern-card--doctorate animate-on-scroll">
                        <div class="program-modern-card__image-wrapper">
                            <?php $programImage = getProgramCarouselImageUrl($program); ?>
                            <img src="<?= esc($programImage) ?>"
                                alt="<?= esc($program['name_th']) ?>"
                                class="program-modern-card__image"
                                loading="lazy"
                                onerror="this.src='<?= base_url('assets/images/placeholder.png') ?>'">
                            <span class="program-modern-card__badge-floating program-modern-card__badge-floating--doctorate">
                                <?= esc(getShortDegree($program['degree_th'] ?? 'ปร.ด.')) ?>
                            </span>
                        </div>
                        <div class="program-modern-card__content">
                            <span class="program-modern-card__degree-label"><?= esc($program['degree_th'] ?? '') ?></span>
                            <h4 class="program-modern-card__title"><?= esc($program['name_th']) ?></h4>
                            <?php if (!empty($program['description'])): ?>
                                <p class="program-modern-card__description">
                                    <?= esc(mb_substr(strip_tags($program['description']), 0, 95)) ?>...
                                </p>
                            <?php endif; ?>
                            <a href="<?= esc($programLink) ?>" class="program-modern-card__link" aria-label="ดูข้อมูลหลักสูตร <?= esc($program['name_th']) ?>">
                                <span>ดูรายละเอียดหลักสูตร</span>
                                <svg class="arrow-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?= base_url('academics') ?>" class="btn btn-primary btn-lg shadow-hover">ดูหลักสูตรทั้งหมด</a>
        </div>
    </div>
</section>

<!-- Service Grid Section (Database & Journals) -->
<section class="section section-blue-light" style="padding: var(--spacing-6) 0;">
    <div class="container">
        <div class="section-header" style="margin-bottom: var(--spacing-4);">
            <h2 class="section-header__title" style="font-size: 1.5rem;">ระบบฐานข้อมูลและวารสาร</h2>
            <p class="section-header__description" style="font-size: 0.9rem; margin-top: 0.5rem;">เข้าถึงระบบสารสนเทศและวารสารวิชาการได้อย่างรวดเร็ว</p>
        </div>

        <div class="service-grid animate-on-scroll">
            <!-- e-Doc (ลิงก์ไป edoc ในไซต์, ด้านล่างใน card มีลิงก์เข้า edoc เดิม) -->
            <div class="service-card" style="display: flex; flex-direction: column;">
                <a href="<?= esc(base_url('edoc')) ?>" style="flex: 1; display: flex; flex-direction: column; text-decoration: none; color: inherit;">
                    <div class="service-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">งานวิชาการ</h3>
                        <span class="service-card__desc">e-Doc System</span>
                    </div>
                </a>
                <a href="http://edoc.sci.uru.ac.th/" target="_blank" rel="noopener" style="font-size: 0.8rem; color: #555; text-decoration: none; margin-top: auto; padding-top: 0.35rem;">เข้าสู่ edoc เดิม</a>
            </div>

            <a href="http://sci.uru.ac.th/scienceadmin" target="_blank" rel="noopener" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ฐานข้อมูลบริหาร</h3>
                    <span class="service-card__desc">Management DB</span>
                </div>
            </a>

            <a href="https://advisor.uru.ac.th" target="_blank" rel="noopener" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">อาจารย์ที่ปรึกษา</h3>
                    <span class="service-card__desc">Advisor System</span>
                </div>
            </a>

            <a href="https://workload.uru.ac.th/" target="_blank" rel="noopener" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ภาระงาน</h3>
                    <span class="service-card__desc">Workload</span>
                </div>
            </a>

            <a href="https://sci.uru.ac.th/docs/qa2568.pdf" target="_blank" rel="noopener" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ประกันคุณภาพ</h3>
                    <span class="service-card__desc">QA System</span>
                </div>
            </a>

            <!-- Journals -->
            <a href="https://ph03.tci-thaijo.org/index.php/ajsas" target="_blank" rel="noopener" class="service-card service-card--journal">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">วารสารวิทยาศาสตร์ฯ</h3>
                    <span class="service-card__desc">AJSAS Journal</span>
                </div>
            </a>

            <a href="http://www.rmj.uru.ac.th/" target="_blank" rel="noopener" class="service-card service-card--journal">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">วารสารคณิตศาสตร์</h3>
                    <span class="service-card__desc">RMS Journal</span>
                </div>
            </a>

            <a href="https://sci.uru.ac.th/academic" target="_blank" rel="noopener" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 0 1 .665 6.479A11.952 11.952 0 0 0 12 20.055a11.952 11.952 0 0 0-6.824-2.998 12.078 12.078 0 0 1 .665-6.479L12 14z"></path>
                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path d="M12 14v6"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ตำแหน่งทางวิชาการ</h3>
                    <span class="service-card__desc">Academic Rank</span>
                </div>
            </a>

            <button type="button" class="service-card service-card--button" id="btn-qr-generator" aria-label="เปิดเครื่องมือสร้าง QR Code">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="3" y="14" width="4" height="4"></rect>
                        <rect x="9" y="14" width="4" height="4"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <line x1="6" y1="14" x2="6" y2="18"></line>
                        <line x1="14" y1="14" x2="14" y2="18"></line>
                        <line x1="18" y1="14" x2="18" y2="21"></line>
                        <line x1="14" y1="18" x2="21" y2="18"></line>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">QR Code Generator</h3>
                    <span class="service-card__desc">สร้าง QR Code</span>
                </div>
            </button>
        </div>
    </div>
</section>

<!-- QR Code Generator Modal -->
<div id="qr-generator-modal" class="qr-modal" role="dialog" aria-labelledby="qr-modal-title" aria-modal="true" hidden>
    <div class="qr-modal__backdrop"></div>
    <div class="qr-modal__box">
        <div class="qr-modal__header">
            <h2 id="qr-modal-title" class="qr-modal__title">สร้าง QR Code</h2>
            <button type="button" class="qr-modal__close" id="qr-modal-close" aria-label="ปิด">×</button>
        </div>
        <div class="qr-modal__body">
            <div class="qr-modal__field">
                <label for="qr-name-input">ชื่อ (ข้อความใน QR Code)</label>
                <input type="text" id="qr-name-input" class="qr-modal__input" placeholder="กรอกชื่อหรือข้อความ" autocomplete="off">
            </div>
            <div class="qr-modal__field qr-modal__field--checkbox">
                <label class="qr-modal__check-label">
                    <input type="checkbox" id="qr-include-logo" checked>
                    <span>ใส่โลโก้ตรงกลาง QR Code</span>
                </label>
            </div>
            <button type="button" id="qr-generate-btn" class="btn btn-primary">สร้าง QR Code</button>
            <div id="qr-output-wrap" class="qr-output-wrap" hidden>
                <div class="qr-output-inner">
                    <div class="qr-canvas-wrap">
                        <canvas id="qr-canvas" width="256" height="256"></canvas>
                        <?php
                    $logoUrl = !empty($settings['logo']) ? (strpos($settings['logo'], 'http') === 0 ? $settings['logo'] : base_url($settings['logo'])) : base_url('assets/images/logo250.png');
                    ?>
                    <img id="qr-center-logo" class="qr-center-logo qr-center-logo--source-only" data-logo-src="<?= esc($logoUrl) ?>" src="<?= esc($logoUrl) ?>" alt="" role="presentation">
                    </div>
                    <p class="qr-output-name" id="qr-output-name"></p>
                    <a id="qr-download-link" class="btn btn-outline btn-sm" download="qrcode.png">ดาวน์โหลด QR Code</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('qr-generator-modal');
    var btnOpen = document.getElementById('btn-qr-generator');
    var btnClose = document.getElementById('qr-modal-close');
    var backdrop = modal && modal.querySelector('.qr-modal__backdrop');
    var nameInput = document.getElementById('qr-name-input');
    var btnGenerate = document.getElementById('qr-generate-btn');
    var outputWrap = document.getElementById('qr-output-wrap');
    var canvas = document.getElementById('qr-canvas');
    var outputName = document.getElementById('qr-output-name');
    var downloadLink = document.getElementById('qr-download-link');
    var logoImg = document.getElementById('qr-center-logo');
    var includeLogoCheck = document.getElementById('qr-include-logo');
    var qrScriptLoaded = false;

    function stripLogoBackground(src, callback) {
        if (!src) { if (callback) callback(src); return; }
        var img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            try {
                var c = document.createElement('canvas');
                c.width = img.naturalWidth;
                c.height = img.naturalHeight;
                var ctx = c.getContext('2d');
                ctx.drawImage(img, 0, 0);
                var d = ctx.getImageData(0, 0, c.width, c.height);
                var data = d.data;
                var threshold = 248;
                for (var i = 0; i < data.length; i += 4) {
                    var r = data[i], g = data[i + 1], b = data[i + 2];
                    if (r >= threshold && g >= threshold && b >= threshold) data[i + 3] = 0;
                }
                ctx.putImageData(d, 0, 0);
                if (callback) callback(c.toDataURL('image/png'));
            } catch (e) { if (callback) callback(src); }
        };
        img.onerror = function() { if (callback) callback(src); };
        img.src = src;
    }

    function openModal() {
        if (!modal) return;
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        nameInput && nameInput.focus();
        var logoSrc = logoImg && (logoImg.getAttribute('data-logo-src') || logoImg.src);
        if (logoImg && logoSrc) stripLogoBackground(logoSrc, function(dataUrl) { logoImg.src = dataUrl; });
    }
    function closeModal() {
        if (!modal) return;
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    if (btnOpen) btnOpen.addEventListener('click', openModal);
    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    function generateQR() {
        var text = nameInput && nameInput.value.trim();
        if (!text) {
            alert('กรุณากรอกชื่อหรือข้อความ');
            return;
        }
        if (!qrScriptLoaded || typeof window.QRCode === 'undefined') {
            alert('กำลังโหลดเครื่องมือสร้าง QR Code กรุณารอสักครู่แล้วลองใหม่');
            return;
        }
        var withLogo = includeLogoCheck && includeLogoCheck.checked;
        var opts = {
            width: 256,
            margin: 2,
            errorCorrectionLevel: withLogo ? 'H' : 'M'
        };
        window.QRCode.toCanvas(canvas, text, opts, function(err) {
            if (err) {
                alert('สร้าง QR Code ไม่สำเร็จ');
                return;
            }
            if (withLogo) {
                compositeLogoIntoCanvas();
            }
            if (outputName) outputName.textContent = text;
            if (outputWrap) outputWrap.removeAttribute('hidden');
            if (downloadLink) {
                downloadLink.href = canvas.toDataURL('image/png');
                downloadLink.download = 'qrcode-' + text.replace(/[^a-zA-Z0-9\u0E00-\u0E7F]/g, '-').substring(0, 30) + '.png';
            }
        });
    }

    function roundRect(ctx, x, y, w, h, r) {
        if (r <= 0) { ctx.rect(x, y, w, h); return; }
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }

    function compositeLogoIntoCanvas() {
        if (!canvas || !logoImg) return;
        var ctx = canvas.getContext('2d');
        if (!ctx) return;
        var size = 256;
        var boxPct = 0.34;
        var logoPct = 0.30;
        var radius = 8;
        var boxSize = Math.round(size * boxPct);
        var logoSize = Math.round(size * logoPct);
        var boxLeft = (size - boxSize) / 2;
        var boxTop = (size - boxSize) / 2;
        var logoLeft = (size - logoSize) / 2;
        var logoTop = (size - logoSize) / 2;
        ctx.fillStyle = '#fff';
        roundRect(ctx, boxLeft, boxTop, boxSize, boxSize, radius);
        ctx.fill();
        if (logoImg.complete && logoImg.naturalWidth) {
            ctx.save();
            roundRect(ctx, boxLeft, boxTop, boxSize, boxSize, radius);
            ctx.clip();
            ctx.drawImage(logoImg, logoLeft, logoTop, logoSize, logoSize);
            ctx.restore();
        }
    }
    if (btnGenerate) btnGenerate.addEventListener('click', generateQR);
    if (nameInput) nameInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') generateQR(); });

    function loadQRScript(url, onDone) {
        var s = document.createElement('script');
        s.src = url;
        s.crossOrigin = 'anonymous';
        s.onload = function() {
            qrScriptLoaded = typeof window.QRCode !== 'undefined';
            if (onDone) onDone();
        };
        s.onerror = function() { if (onDone) onDone(); };
        document.head.appendChild(s);
    }
    loadQRScript('https://cdn.jsdelivr.net/npm/qrcode@1.2.2/build/qrcode.min.js', function() {
        if (!qrScriptLoaded) loadQRScript('https://unpkg.com/qrcode@1.2.2/build/qrcode.min.js');
    });
})();
</script>

<!-- Campus News Section (ข่าวประชาสัมพันธ์ - แบบกะทัดรัด เนื้อหาครบ) -->
<section class="news-section news-section--compact section-white-pattern">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวประชาสัมพันธ์</h2>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                <a href="<?= base_url('events') ?>" class="btn btn-outline btn-sm" style="white-space: nowrap;">
                    กิจกรรม / Event ที่จะเกิดขึ้น
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 0.25rem; vertical-align: -2px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </a>
                <a href="<?= base_url('news') ?>" class="news-section__link">
                    ดูข่าวทั้งหมด
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <div class="featured-news" data-tag="general" data-limit="6">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดข่าว...</p>
            </div>
        </div>
    </div>
</section>


<!-- Research News Section (ข่าว tag งานวิจัย) -->
<section class="news-section news-section--compact section-blue-light">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวงานวิจัย</h2>
            <a href="<?= base_url('news?tag=research') ?>" class="news-section__link">
                ดูทั้งหมด
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <div class="featured-news" data-tag="research" data-limit="6">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดข่าว...</p>
            </div>
        </div>
    </div>
</section>

<!-- Student Activities Section (แบบกะทัดรัด) -->
<section class="news-section news-section--compact section-cream">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวกิจกรรมนักศึกษา</h2>
            <a href="<?= base_url('news') ?>" class="news-section__link">
                ดูทั้งหมด
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <div class="featured-news" data-tag="student_activity" data-limit="6">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดข่าว...</p>
            </div>
        </div>
    </div>
</section>

<script>
    // Hero Carousel JavaScript
    (function() {
        let currentSlide = 0;
        let autoplayInterval;
        const autoplayDelay = 6000; // 6 seconds

        function getSlides() {
            return document.querySelectorAll('.hero-carousel__slide');
        }

        function getDots() {
            return document.querySelectorAll('.hero-carousel__dot');
        }

        window.heroCarouselGoto = function(index) {
            const slides = getSlides();
            const dots = getDots();

            if (slides.length === 0) return;

            // Wrap around
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            // Update slides
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });

            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            currentSlide = index;
            resetAutoplay();
        };

        window.heroCarouselNav = function(direction) {
            heroCarouselGoto(currentSlide + direction);
        };

        function resetAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
            }
            autoplayInterval = setInterval(() => {
                heroCarouselNav(1);
            }, autoplayDelay);
        }

        // Touch/Swipe support
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('heroCarousel');
            if (!carousel) return;

            let touchStartX = 0;
            let touchEndX = 0;

            carousel.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, {
                passive: true
            });

            carousel.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > 50) { // Minimum swipe distance
                    if (diff > 0) {
                        heroCarouselNav(1); // Swipe left = next
                    } else {
                        heroCarouselNav(-1); // Swipe right = prev
                    }
                }
            }, {
                passive: true
            });

            // Start autoplay
            resetAutoplay();

            // Pause on hover
            carousel.addEventListener('mouseenter', () => {
                if (autoplayInterval) clearInterval(autoplayInterval);
            });

            carousel.addEventListener('mouseleave', () => {
                resetAutoplay();
            });
        });
    })();
</script>

<!-- Quality Assurance & Academic Journals Section -->
<!-- <section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">งานประกันคุณภาพและวารสารวิชาการ</span>
            <h2 class="section-header__title">ระบบฐานข้อมูลและวารสาร</h2>
        </div>

        <div class="qa-compact-grid">
            <a href="#" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 12h8M12 8v8"></path>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>QA-SCIURU</h4>
                    <p>งานประกันคุณภาพ</p>
                </div>
            </a>

            <a href="https://scitech.kpru.ac.th/qa/login.php" target="_blank" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--dark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>ระบบฐานข้อมูลงานประกันคุณภาพ</h4>
                    <p>เข้าสู่ระบบ</p>
                </div>
            </a>

            <a href="#" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--dark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>ฐานข้อมูลผู้ทรงคุณวุฒิ</h4>
                    <p>เครือข่าย มรภ.</p>
                </div>
            </a>

            <a href="#" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--dark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>ฐานข้อมูลวารสารของเครือข่าย</h4>
                    <p>กลุ่ม มรภ. แห่งประเทศไทย</p>
                </div>
            </a>

            <a href="https://ph03.tci-thaijo.org/index.php/ajsas" target="_blank" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--accent">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>AJSAS</h4>
                    <p>วารสารวิชาการวิทยาศาสตร์ฯ</p>
                </div>
            </a>
        </div>
    </div>
</section> -->

<!-- Executive Highlight Section (ดึงจาก personnel) -->
<section class="section section-slate-gradient">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ผู้บริหารคณะ</span>
            <h2 class="section-header__title">ทีมผู้บริหาร</h2>
        </div>

        <?php
        $getPersonnelImageUrl = function ($p) {
            $img = isset($p['image']) ? trim((string) $p['image']) : '';
            if ($img === '') return '';
            if (strpos($img, 'http') === 0) return $img;
            return base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $img)));
        };
        $dean = $dean ?? null;
        $viceDeans = $vice_deans ?? [];
        ?>
        <div class="executive-highlight">
            <?php if ($dean): ?>
                <!-- Dean Card (จาก personnel tier 1) -->
                <div class="dean-card animate-on-scroll">
                    <div class="dean-card__image">
                        <?php
                        $deanImg = $getPersonnelImageUrl($dean);
                        $deanName = trim($dean['name'] ?? '');
                        if ($deanImg): ?>
                            <img src="<?= esc($deanImg) ?>" alt="<?= esc($deanName) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($deanName) ?>&background=1e3a5f&color=fff&size=200&font-size=0.4'">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($deanName) ?>&background=1e3a5f&color=fff&size=200&font-size=0.4" alt="<?= esc($deanName) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="dean-card__content">
                        <div class="dean-card__badge"><?= esc($dean['position'] ?? 'คณบดี') ?></div>
                        <h3 class="dean-card__name"><?= esc($deanName) ?></h3>
                        <p class="dean-card__title"><?= esc($dean['position_en'] ?? 'Dean, Faculty of Science and Technology') ?></p>
                        <?php if (!empty($dean['bio'])): ?>
                            <p class="dean-card__message"><?= esc(mb_substr(strip_tags($dean['bio']), 0, 200)) ?><?= mb_strlen(strip_tags($dean['bio'] ?? '')) > 200 ? '…' : '' ?></p>
                        <?php else: ?>
                            <p class="dean-card__message">คณะวิทยาศาสตร์และเทคโนโลยี มุ่งมั่นผลิตบัณฑิตที่มีคุณภาพ พร้อมทั้งเป็นแหล่งเรียนรู้และบริการวิชาการแก่ชุมชนท้องถิ่น</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Vice Deans (จาก personnel tier 2) -->
            <?php if (!empty($viceDeans)): ?>
                <div class="vice-deans">
                    <?php foreach ($viceDeans as $vd):
                        $vdName = trim($vd['name'] ?? '');
                        $vdImg = $getPersonnelImageUrl($vd);
                    ?>
                        <div class="vice-dean-card animate-on-scroll">
                            <?php if ($vdImg): ?>
                                <img src="<?= esc($vdImg) ?>" alt="<?= esc($vdName) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($vdName) ?>&background=2d7d46&color=fff&size=80&font-size=0.4'">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($vdName) ?>&background=2d7d46&color=fff&size=80&font-size=0.4" alt="<?= esc($vdName) ?>">
                            <?php endif; ?>
                            <div class="vice-dean-card__info">
                                <span class="vice-dean-card__position"><?= esc($vd['position'] ?? 'รองคณบดี') ?></span>
                                <span class="vice-dean-card__name"><?= esc($vdName) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?= base_url('executives') ?>" class="btn btn-outline">ดูโครงสร้างองค์กรทั้งหมด</a>
        </div>
    </div>
</section>

<!-- Event Coming Up Section -->
<section class="section section-blue-light">
    <div class="container">
        <div class="section-header" style="margin-bottom: var(--spacing-6);">
            <span class="section-header__subtitle">กิจกรรม</span>
            <h2 class="section-header__title">กิจกรรมที่จะมาถึง</h2>
            <p class="section-header__description">Events Coming Up — กิจกรรมและข่าวสารล่าสุดที่กำลังจะมาถึง</p>
        </div>
        <div id="events-coming-up" class="events-coming-up" data-limit="4">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดกิจกรรม...</p>
            </div>
        </div>
        <div class="text-center" style="margin-top: var(--spacing-6);">
            <a href="<?= base_url('events') ?>" class="btn btn-primary">ดูกิจกรรมทั้งหมด</a>
        </div>
    </div>
</section>

<!-- Campus Life Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ชีวิตในมหาวิทยาลัย</span>
            <h2 class="section-header__title">ชุมชนที่มีชีวิตชีวา</h2>
            <p class="section-header__description">
                สัมผัสวัฒนธรรมในมหาวิทยาลัยที่หลากหลาย มีกิจกรรม องค์กร และโอกาสในการเติบโต
            </p>
        </div>

        <div class="grid grid-3">
            <article class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/student_activities.png') ?>" alt="Student Activities" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">กิจกรรมนักศึกษา</h3>
                    <p class="card__excerpt">
                        ค้นพบวิธีมากมายในการเชื่อมต่อ เติบโต และสนุกสนาน
                    </p>
                </div>
            </article>

            <article class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/wellness_recreation.png') ?>" alt="Wellness Center" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">สุขภาพและสันทนาการ</h3>
                    <p class="card__excerpt">
                        สิ่งอำนวยความสะดวกและโปรแกรมที่ทันสมัยเพื่อสนับสนุนสุขภาพของคุณ
                    </p>
                </div>
            </article>

            <article class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/community_service.png') ?>" alt="Community Events" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">บริการวิชาการ</h3>
                    <p class="card__excerpt">
                        สร้างความแตกต่างผ่านการเรียนรู้เพื่อบริการชุมชน
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Contact Info Footer -->
<section class="section section-light">
    <div class="container">
        <div class="grid grid-2" style="gap: 2rem;">
            <div>
                <h3 style="margin-bottom: 1rem;">ติดต่อเรา</h3>
                <p style="margin-bottom: 0.5rem;">
                    <strong><?= esc($siteName) ?></strong><br>
                    <?= esc($university) ?>
                </p>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    <?= esc($settings['address_th'] ?? '27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000') ?>
                </p>
            </div>
            <div>
                <h3 style="margin-bottom: 1rem;">ข้อมูลติดต่อ</h3>
                <p style="margin-bottom: 0.5rem;">
                    <strong>โทรศัพท์:</strong> <?= esc($settings['phone'] ?? '055-411096') ?>
                </p>
                <p style="margin-bottom: 0.5rem;">
                    <strong>อีเมล:</strong> <?= esc($settings['email'] ?? 'sci@uru.ac.th') ?>
                </p>
                <?php if (!empty($settings['facebook'])): ?>
                    <p>
                        <a href="<?= esc($settings['facebook']) ?>" target="_blank" class="btn btn-sm btn-outline">
                            Facebook
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>


    // Load news sections via AJAX
    (function() {
        const baseUrl = (window.BASE_URL || '<?= base_url() ?>').replace(/\/?$/, '/');

        function escHtml(s) {
            if (s == null) return '';
            const div = document.createElement('div');
            div.textContent = String(s);
            return div.innerHTML;
        }

        // Tag labels (slug จาก news_tags)
        const tagLabels = {
            'research': 'งานวิจัย',
            'research_grant': 'ทุนวิจัย',
            'student_activity': 'กิจกรรมนักศึกษา',
            'general': 'ข่าวทั่วไป'
        };

        // Format date helper (Thai format)
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = date.getDate();
            const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${day} ${month} ${year}`;
        }

        // Render news card (all cards are equal size now)
        function renderNewsCard(article) {
            if (!article || article.id == null) {
                return '';
            }

            let imageUrl = '';

            // Check if featured_image exists and is not empty
            // API returns empty string '' when no image, so we need to check explicitly
            const hasImage = article.featured_image !== null &&
                article.featured_image !== undefined &&
                article.featured_image !== '' &&
                typeof article.featured_image === 'string' &&
                article.featured_image.trim() !== '';

            if (hasImage) {
                // Use image from database (already formatted with base_url by API)
                imageUrl = article.featured_image;
            } else {
                // Fallback image only when no image in database
                imageUrl = baseUrl + 'assets/images/placeholder.png';
            }

            // Use primary tag or first available tag for categorization
            let primaryTag = 'general';
            let primaryTagLabel = 'ข่าวทั่วไป';

            if (article.primary_tag) {
                primaryTag = article.primary_tag;
                primaryTagLabel = tagLabels[primaryTag] || tagLabels['general'];
            } else if (article.tags && article.tags.length > 0) {
                primaryTag = article.tags[0].slug;
                primaryTagLabel = article.tags[0].name;
            }

            const rawTitle = article.title != null ? String(article.title) : '';
            const title = rawTitle.length > 100 ? rawTitle.substring(0, 100) + '...' : rawTitle;

            // Generate tags HTML if tags are available
            let tagsHtml = '';
            if (article.tags && article.tags.length > 0) {
                tagsHtml = '<div class="card__tags">';
                article.tags.forEach(tag => {
                    tagsHtml += `<span class="card__tag">${escHtml(tag.name)}</span>`;
                });
                tagsHtml += '</div>';
            }

            const href = `${baseUrl}news/${article.id}`;
            return `
                <a href="${href}" class="card animate-on-scroll" style="text-decoration:none;color:inherit;display:block;">
                    <img src="${escHtml(imageUrl)}" alt="${escHtml(rawTitle)}" class="card__image" loading="lazy" onerror="this.onerror=null; this.src='${baseUrl}assets/images/placeholder.png';">
                    <div class="card__content card__content--${escHtml(primaryTag)}">
                        <span class="card__category">${escHtml(primaryTagLabel)}</span>
                        <h3 class="card__title">${escHtml(title)}</h3>
                        ${article.excerpt ? `<p class="card__excerpt">${escHtml(String(article.excerpt).substring(0, 130))}${String(article.excerpt).length > 130 ? '…' : ''}</p>` : ''}
                        ${tagsHtml}
                        <div class="card__meta">
                            <span>${formatDate(article.published_at)}</span>
                        </div>
                    </div>
                </a>
            `;
        }

        function buildNewsApiUrl(tag, limit) {
            if (tag === 'research') {
                return `${baseUrl}api/news/research?limit=${limit}`;
            }
            if (tag) {
                return `${baseUrl}api/news/tag/${encodeURIComponent(tag)}?limit=${limit}`;
            }
            return `${baseUrl}api/news?limit=${limit}`;
        }

        function fetchNewsJson(url) {
            return fetch(url, { headers: { Accept: 'application/json' } }).then(function(response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ' for ' + url);
                }
                return response.json();
            });
        }

        function renderNewsList(container, articles, limit) {
            let html = '';
            const max = Math.min(limit, articles.length);
            for (let i = 0; i < max; i++) {
                html += renderNewsCard(articles[i]);
            }
            if (!html) {
                return false;
            }
            container.innerHTML = html;
            if (typeof initAnimations === 'function') {
                initAnimations(container);
            }
            return true;
        }

        function loadNewsSection(container) {
            const tag = container.getAttribute('data-tag');
            const limit = parseInt(container.getAttribute('data-limit'), 10) || 6;
            const primaryUrl = buildNewsApiUrl(tag, limit);
            const fallbackUrl = `${baseUrl}api/news?limit=${limit}`;

            function tryApply(result) {
                if (result && result.success && Array.isArray(result.data) && result.data.length > 0) {
                    return renderNewsList(container, result.data, limit);
                }
                return false;
            }

            fetchNewsJson(primaryUrl)
                .then(function(result) {
                    if (tryApply(result)) {
                        return null;
                    }
                    if (tag === 'general') {
                        return fetchNewsJson(fallbackUrl);
                    }
                    container.innerHTML = '<div class="text-center py-8"><p class="text-muted">ยังไม่มีข่าวในหมวดหมู่นี้</p></div>';
                    return null;
                })
                .then(function(result) {
                    if (!result) {
                        return;
                    }
                    if (!tryApply(result)) {
                        container.innerHTML = '<div class="text-center py-8"><p class="text-muted">ยังไม่มีข่าวในหมวดหมู่นี้</p></div>';
                    }
                })
                .catch(function(error) {
                    console.error('Error loading news:', primaryUrl, error);
                    fetchNewsJson(fallbackUrl)
                        .then(function(result) {
                            if (!tryApply(result)) {
                                container.innerHTML = '<div class="text-center py-8"><p class="text-muted">เกิดข้อผิดพลาดในการโหลดข่าว</p></div>';
                            }
                        })
                        .catch(function(fallbackErr) {
                            console.error('News fallback failed:', fallbackUrl, fallbackErr);
                            container.innerHTML = '<div class="text-center py-8"><p class="text-muted">เกิดข้อผิดพลาดในการโหลดข่าว</p></div>';
                        });
                });
        }

        // Format date for event card (day + month short)
        function formatEventDate(dateString) {
            if (!dateString) return {
                day: '',
                month: ''
            };
            const date = new Date(dateString);
            const day = date.getDate();
            const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            const month = months[date.getMonth()];
            return {
                day: String(day),
                month
            };
        }

        // Render event card from table `events` (event_date, link to /events/id)
        function renderEventCard(ev) {
            const dateStr = ev.event_date || ev.published_at;
            const {
                day,
                month
            } = formatEventDate(dateStr);
            const rawTitle = ev.title || '';
            const title = rawTitle.length > 80 ? rawTitle.substring(0, 80) + '...' : rawTitle;
            const rawExcerpt = ev.excerpt || '';
            const excerpt = rawExcerpt.length > 120 ? rawExcerpt.substring(0, 120) + '...' : rawExcerpt;
            const linkUrl = ev.event_date ? `${baseUrl}events/${ev.id}` : `${baseUrl}news/${ev.id}`;
            return `
                <a href="${linkUrl}" class="event-card animate-on-scroll">
                    <div class="event-card__date">
                        <span class="event-card__day">${escHtml(day)}</span>
                        <span class="event-card__month">${escHtml(month)}</span>
                    </div>
                    <div class="event-card__body">
                        <h3 class="event-card__title">${escHtml(title)}</h3>
                        ${excerpt ? `<p class="event-card__excerpt">${escHtml(excerpt)}</p>` : ''}
                        <span class="event-card__link">ดูรายละเอียด →</span>
                    </div>
                </a>
            `;
        }

        // Load Events Coming Up: ใช้ตาราง events ก่อน (api/events/upcoming) ถ้าไม่มีค่อยใช้ข่าวล่าสุด
        function loadEventsComingUp() {
            const container = document.getElementById('events-coming-up');
            if (!container) return;
            const limit = parseInt(container.getAttribute('data-limit')) || 4;

            fetch(`${baseUrl}api/events/upcoming?limit=${limit}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data && result.data.length > 0) {
                        const events = result.data;
                        let html = '';
                        for (let i = 0; i < events.length; i++) {
                            html += renderEventCard(events[i]);
                        }
                        container.innerHTML = html;
                        if (typeof initAnimations === 'function') initAnimations();
                        return;
                    }
                    // ไม่มีกิจกรรมจากตาราง events — fallback ใช้ข่าวล่าสุด
                    return fetch(`${baseUrl}api/news?limit=${limit}`)
                        .then(r => r.json())
                        .then(newsResult => {
                            if (newsResult.success && newsResult.data && newsResult.data.length > 0) {
                                let html = '';
                                for (let i = 0; i < newsResult.data.length; i++) {
                                    html += renderEventCard(newsResult.data[i]);
                                }
                                container.innerHTML = html;
                                if (typeof initAnimations === 'function') initAnimations();
                            } else {
                                container.innerHTML = '<div class="text-center py-8"><p class="text-muted">ยังไม่มีกิจกรรมในขณะนี้</p></div>';
                            }
                        });
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    container.innerHTML = '<div class="text-center py-8"><p class="text-muted">เกิดข้อผิดพลาดในการโหลดกิจกรรม</p></div>';
                });
        }

        // Load all news sections when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const newsSections = document.querySelectorAll('.featured-news[data-tag]');

            // Load each section with a small delay to stagger requests
            newsSections.forEach((section, index) => {
                setTimeout(() => {
                    loadNewsSection(section);
                }, index * 200); // 200ms delay between each request
            });

            // Load Events Coming Up
            setTimeout(loadEventsComingUp, 400);
        });
    })();
</script>

<style>
    /* News card tags */
    .card__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin: 0.5rem 0;
    }

    .card__tag {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        background-color: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 0.25rem;
        text-transform: none;
        line-height: 1.4;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .card__tag:hover {
        background-color: #e2e8f0;
        color: #334155;
    }

    /* Research tag specific styling */
    .card__tag.research-tag {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .card__tag.research-tag:hover {
        background-color: #bfdbfe;
        color: #1e3a8a;
    }
</style>

<script>
    // Add research tag styling after tags are rendered
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const researchTags = document.querySelectorAll('.card__tag');
            researchTags.forEach(tag => {
                const tagText = tag.textContent.trim();
                if (tagText.includes('งานวิจัย') || tagText.includes('วิจัย') || tagText.includes('ทุนวิจัย')) {
                    tag.classList.add('research-tag');
                }
            });
        }, 1000); // Wait for tags to be loaded

    });
</script>

<?= $this->endSection() ?>