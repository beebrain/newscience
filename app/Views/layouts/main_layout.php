<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=5">
    <meta name="theme-color" content="#1a1a1a">
    <meta name="description" content="<?= $meta_description ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์' ?>">
    <title><?= $page_title ?? 'คณะวิทยาศาสตร์และเทคโนโลยี' ?> | มหาวิทยาลัยราชภัฏอุตรดิตถ์</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">

    <!-- ฟอนต์ Sarabun + Noto Sans Thai (โหลดจากโปรเจกต์) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>?v=<?= (defined('FCPATH') && is_file(FCPATH . 'assets/css/fonts.css')) ? filemtime(FCPATH . 'assets/css/fonts.css') : '1' ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sarabun': ['Sarabun', 'Noto Sans Thai', 'sans-serif'],
                    },
                    colors: {
                        primary: '#eab308',
                        'primary-dark': '#ca8a04',
                        secondary: '#2d7d46',
                        accent: '#eab308',
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php
    $is_home = ($active_page ?? '') === 'home';
    $css_ver = (defined('FCPATH') && is_file(FCPATH . 'assets/css/base.css'))
        ? filemtime(FCPATH . 'assets/css/base.css') : '1';
    ?>
    <!-- Central CSS: theme + base + components (โหลด parallel), home/pages ตามหน้าที่ใช้ -->
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>?v=<?= $css_ver ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/base.css') ?>?v=<?= $css_ver ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>?v=<?= $css_ver ?>">
    <?php if ($is_home): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/home.css') ?>?v=<?= $css_ver ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/pages.css') ?>?v=<?= $css_ver ?>">
    <?php endif; ?>
    <style>
        html {
            --hero-bg-url: url('<?= base_url('assets/images/hero_background.png') ?>');
        }

        /* ซ่อนเนื้อหาจนกว่า CSS จะโหลดครบ (ป้องกัน FOUC) */
        .css-loading {
            opacity: 0;
        }

        .css-loaded {
            opacity: 1;
            transition: opacity 0.15s ease-in;
        }

        @media (prefers-reduced-motion: reduce) {
            .css-loaded {
                transition: none;
            }
        }

        /* Fallback: ถ้า JavaScript ปิด ให้แสดงเนื้อหาเลย */
        noscript+body,
        .no-js body {
            opacity: 1 !important;
        }
    </style>
    <script>
        (function() {
            function showContent() {
                if (document.body) {
                    document.body.classList.remove('css-loading');
                    document.body.classList.add('css-loaded');
                }
            }

            function waitForStyles() {
                var links = document.querySelectorAll('link[rel="stylesheet"]');
                var total = links.length;
                if (total === 0) {
                    showContent();
                    return;
                }
                var loaded = 0;

                function checkDone() {
                    loaded++;
                    if (loaded >= total) showContent();
                }
                for (var i = 0; i < links.length; i++) {
                    var link = links[i];
                    if (link.sheet) checkDone();
                    else link.onload = checkDone;
                }
                setTimeout(showContent, 4000);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', waitForStyles);
            } else {
                waitForStyles();
            }
        })();
    </script>
</head>

<body class="css-loading">
    <script>
        (function() {
            var w = window.innerWidth;
            var h = window.innerHeight;
            var dpr = window.devicePixelRatio || 1;
            var body = document.body;
            if (w < 480) body.classList.add('is-small-phone');
            else if (w < 640) body.classList.add('is-phone');
            else if (w < 768) body.classList.add('is-phablet');
            else if (w < 1024) body.classList.add('is-tablet');
            else if (w < 1200) body.classList.add('is-laptop');
            else if (w < 1440) body.classList.add('is-desktop');
            else body.classList.add('is-large-desktop');
            body.classList.add(w < 768 ? 'is-mobile' : 'is-desktop-view');
            if ('ontouchstart' in window || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0)) body.classList.add('is-touch');
            else body.classList.add('is-no-touch');
            if (dpr >= 2) {
                body.classList.add('is-hidpi');
                if (dpr >= 3) body.classList.add('is-retina-3x');
            }
            body.classList.add(w > h ? 'is-landscape' : 'is-portrait');
            document.documentElement.style.setProperty('--device-width', w + 'px');
            document.documentElement.style.setProperty('--device-height', h + 'px');
            document.documentElement.style.setProperty('--device-dpr', String(dpr));
            document.documentElement.style.setProperty('--screen-width', (window.screen && window.screen.width ? window.screen.width : w) + 'px');
            document.documentElement.style.setProperty('--screen-height', (window.screen && window.screen.height ? window.screen.height : h) + 'px');
        })();
    </script>
    <!-- Skip link for main content (accessibility) -->
    <a href="#main-content" class="skip-link">ข้ามไปเนื้อหา</a>

    <!-- Header (รวม Topbar เข้าใน Menubar) -->
    <header class="header">
        <div class="container">
            <!-- Logo -->
            <a href="<?= base_url() ?>" class="logo">
                <?php
                $logo = !empty($settings['logo']) ? $settings['logo'] : base_url('assets/images/logo250.png');
                ?>
                <img src="<?= esc($logo) ?>" alt="Logo คณะวิทยาศาสตร์และเทคโนโลยี" class="logo__img" style="height: 50px; width: auto;">
                <div class="logo__text">
                    <?= esc($settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?>
                    <span><?= esc($settings['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?></span>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="nav">
                <?php $navSvg = '<svg style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-left:2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>'; ?>
                <ul class="nav__list">
                    <li><a href="<?= base_url() ?>" class="nav__link <?= ($active_page ?? '') === 'home' ? 'active' : '' ?>">หน้าแรก</a></li>
                    <li><a href="<?= base_url('academics') ?>" class="nav__link <?= ($active_page ?? '') === 'academics' ? 'active' : '' ?>">หลักสูตร</a></li>
                    <li><a href="<?= base_url('research') ?>" class="nav__link <?= ($active_page ?? '') === 'research' ? 'active' : '' ?>">วิจัย</a></li>
                    <li><a href="<?= base_url('news') ?>" class="nav__link <?= ($active_page ?? '') === 'news' ? 'active' : '' ?>">ข่าว</a></li>
                    <li class="nav__item--has-dropdown">
                        <a href="<?= base_url('personnel') ?>" class="nav__link <?= in_array($active_page ?? '', ['personnel', 'executives']) ? 'active' : '' ?>">บุคลากร <?= $navSvg ?></a>
                        <ul class="nav__dropdown">
                            <li><a href="<?= base_url('executives') ?>" class="nav__dropdown-link">ผู้บริหาร</a></li>
                            <li><a href="<?= base_url('personnel') ?>" class="nav__dropdown-link">บุคลากร</a></li>
                        </ul>
                    </li>
                    <li class="nav__item--has-dropdown">
                        <a href="#" class="nav__link">บริการ <?= $navSvg ?></a>
                        <ul class="nav__dropdown">
                            <li class="nav__item--has-dropdown nav__item--has-subdropdown">
                                <span class="nav__dropdown-link nav__dropdown-link--subtrigger">ระบบและวารสาร</span>
                                <ul class="nav__dropdown nav__dropdown--sub">
                                    <li><a href="http://edoc.sci.uru.ac.th/" target="_blank" class="nav__dropdown-link" rel="noopener">งานวิชาการ (e-Doc)</a></li>
                                    <li><a href="http://sci.uru.ac.th/scienceadmin" target="_blank" class="nav__dropdown-link" rel="noopener">ฐานข้อมูลบริหาร</a></li>
                                    <li><a href="https://advisor.uru.ac.th" target="_blank" class="nav__dropdown-link" rel="noopener">อาจารย์ที่ปรึกษา</a></li>
                                    <li><a href="https://workload.uru.ac.th/" target="_blank" class="nav__dropdown-link" rel="noopener">ภาระงาน</a></li>
                                    <li><a href="https://sci.uru.ac.th/docs/qa2568.pdf" target="_blank" class="nav__dropdown-link" rel="noopener">ประกันคุณภาพ</a></li>
                                    <li><a href="https://ph03.tci-thaijo.org/index.php/ajsas" target="_blank" class="nav__dropdown-link" rel="noopener">วารสารวิทยาศาสตร์ฯ (AJSAS)</a></li>
                                    <li><a href="http://www.rmj.uru.ac.th/" target="_blank" class="nav__dropdown-link" rel="noopener">วารสารคณิตศาสตร์ (RMS)</a></li>
                                    <li><a href="https://sci.uru.ac.th/academic" target="_blank" class="nav__dropdown-link" rel="noopener">ตำแหน่งทางวิชาการ</a></li>
                                </ul>
                            </li>
                            <li class="nav__item--has-dropdown nav__item--has-subdropdown">
                                <span class="nav__dropdown-link nav__dropdown-link--subtrigger">เว็บหน่วยงาน</span>
                                <ul class="nav__dropdown nav__dropdown--sub">
                                    <li><a href="http://202.29.52.60/~dicenter" target="_blank" class="nav__dropdown-link" rel="noopener">ศูนย์ดิจิทัลเพื่อพัฒนาท้องถิ่น</a></li>
                                    <li><a href="https://www.facebook.com/ScienceRMUURU" target="_blank" class="nav__dropdown-link" rel="noopener">หน่วยจัดการงานวิจัยและพันธกิจสัมพันธ์</a></li>
                                    <li><a href="https://sci.uru.ac.th/csrm" target="_blank" class="nav__dropdown-link" rel="noopener">ศูนย์ประสานงานโครงการ CSRM</a></li>
                                    <li><a href="http://scirmu.sci.uru.ac.th/" target="_blank" class="nav__dropdown-link" rel="noopener">ศูนย์พลังงานและสิ่งแวดล้อม</a></li>
                                    <li><a href="https://sci.uru.ac.th/scienceweek" target="_blank" class="nav__dropdown-link" rel="noopener">สัปดาห์วิทยาศาสตร์แห่งชาติ</a></li>
                                </ul>
                            </li>
                            <li class="nav__item--has-dropdown nav__item--has-subdropdown">
                                <span class="nav__dropdown-link nav__dropdown-link--subtrigger">ลิงก์ด่วน</span>
                                <ul class="nav__dropdown nav__dropdown--sub">
                                    <?php
                                    $quickLinks = [];
                                    foreach (($settings ?? []) as $k => $v) {
                                        if (!is_string($k) || strpos($k, 'quick_link_') !== 0) {
                                            continue;
                                        }
                                        $idx = (int) preg_replace('/[^0-9]/', '', $k);
                                        $payload = null;
                                        if (is_string($v) && $v !== '') {
                                            $decoded = json_decode($v, true);
                                            if (is_array($decoded)) {
                                                $payload = $decoded;
                                            }
                                        }
                                        if (!is_array($payload)) {
                                            continue;
                                        }
                                        $name = trim((string) ($payload['name_th'] ?? $payload['name_en'] ?? ''));
                                        $url = trim((string) ($payload['url'] ?? ''));
                                        if ($name === '' || $url === '') {
                                            continue;
                                        }
                                        $quickLinks[] = [
                                            'idx' => $idx,
                                            'name' => $name,
                                            'url' => $url,
                                        ];
                                    }

                                    usort($quickLinks, static function ($a, $b) {
                                        return ($a['idx'] ?? 0) <=> ($b['idx'] ?? 0);
                                    });
                                    ?>

                                    <?php foreach ($quickLinks as $ql): ?>
                                        <?php
                                        $href = $ql['url'];
                                        $isExternal = (strpos($href, 'http://') === 0 || strpos($href, 'https://') === 0);
                                        ?>
                                        <li>
                                            <a href="<?= esc($href) ?>" class="nav__dropdown-link" <?= $isExternal ? 'target="_blank" rel="noopener"' : '' ?>>
                                                <?= esc($ql['name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>

                                    <li><a href="<?= base_url('dashboard') ?>" class="nav__dropdown-link">การจัดการ (Dashboard)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="nav__item--has-dropdown">
                        <a href="<?= base_url('support-documents') ?>" class="nav__link <?= in_array(($active_page ?? ''), ['support-documents', 'official-documents', 'promotion-criteria']) ? 'active' : '' ?>">เอกสาร <?= $navSvg ?></a>
                        <ul class="nav__dropdown">
                            <li><a href="<?= base_url('support-documents') ?>" class="nav__dropdown-link">แบบฟอร์มดาวน์โหลด</a></li>
                            <li><a href="<?= base_url('internal-documents') ?>" class="nav__dropdown-link">เอกสารภายในมหาวิทยาลัย</a></li>
                            <li><a href="<?= base_url('official-documents') ?>" class="nav__dropdown-link">คำสั่ง/ประกาศ/ระเบียบ</a></li>
                            <li><a href="<?= base_url('promotion-criteria') ?>" class="nav__dropdown-link">เกณฑ์การประเมินบุคคล</a></li>
                        </ul>
                    </li>
                    <li class="nav__item--has-dropdown">
                        <a href="<?= base_url('about') ?>" class="nav__link <?= in_array($active_page ?? '', ['about', 'contact']) ? 'active' : '' ?>">เกี่ยวกับ <?= $navSvg ?></a>
                        <ul class="nav__dropdown">
                            <li><a href="<?= base_url('about') ?>" class="nav__dropdown-link">เกี่ยวกับเรา</a></li>
                            <li><a href="<?= base_url('contact') ?>" class="nav__dropdown-link">ติดต่อ</a></li>
                        </ul>
                    </li>
                    <?php if (session()->get('admin_logged_in')): ?>
                    <li>
                        <a href="<?= base_url('dashboard') ?>" class="nav__link nav__link--user" title="ไปที่ Dashboard"><?= esc(session()->get('admin_name') ?: session()->get('admin_email')) ?></a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/logout') ?>" class="nav__link nav__link--logout">ออกจากระบบ</a>
                    </li>
                    <?php else: ?>
                    <li>
                        <a href="<?= base_url('admin/login') ?>" class="nav__link nav__link--login">เข้าสู่ระบบ</a>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- Search Button -->
                <button class="nav__search" aria-label="ค้นหา">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>

                <!-- Mobile Menu Toggle -->
                <button class="menu-toggle" aria-label="เมนู">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="mobile-nav__header">
            <a href="<?= base_url() ?>" class="logo">
                <img src="<?= esc($logo) ?>" alt="Logo คณะวิทยาศาสตร์และเทคโนโลยี" class="logo__img" style="height: 40px; width: auto;">
                <div class="logo__text">คณะวิทยาศาสตร์ฯ</div>
            </a>
            <button class="mobile-nav__close" aria-label="ปิดเมนู">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <ul class="mobile-nav__list">
            <li><a href="#" class="mobile-nav__link">นักศึกษา</a></li>
            <li><a href="#" class="mobile-nav__link">บุคลากร</a></li>
            <li><a href="#" class="mobile-nav__link">ศิษย์เก่า</a></li>
            <li><a href="http://old.sci.uru.ac.th" class="mobile-nav__link" target="_blank" rel="noopener noreferrer">เว็บคณะเดิม</a></li>
            <li class="mobile-nav__divider" role="presentation"></li>
            <li><a href="<?= base_url() ?>" class="mobile-nav__link">หน้าแรก</a></li>
            <li><a href="<?= base_url('academics') ?>" class="mobile-nav__link">หลักสูตร</a></li>
            <li><a href="<?= base_url('research') ?>" class="mobile-nav__link">วิจัย</a></li>
            <li><a href="<?= base_url('news') ?>" class="mobile-nav__link">ข่าว</a></li>
            <li class="mobile-nav__header-item">บุคลากร</li>
            <li><a href="<?= base_url('executives') ?>" class="mobile-nav__link" style="padding-left: 2rem;">ผู้บริหาร</a></li>
            <li><a href="<?= base_url('personnel') ?>" class="mobile-nav__link" style="padding-left: 2rem;">บุคลากร</a></li>
            <li class="mobile-nav__header-item">เอกสาร</li>
            <li><a href="<?= base_url('support-documents') ?>" class="mobile-nav__link" style="padding-left: 2rem;">แบบฟอร์มดาวน์โหลด</a></li>
            <li><a href="<?= base_url('official-documents') ?>" class="mobile-nav__link" style="padding-left: 2rem;">คำสั่ง/ประกาศ/ระเบียบ</a></li>
            <li><a href="<?= base_url('promotion-criteria') ?>" class="mobile-nav__link" style="padding-left: 2rem;">เกณฑ์การประเมินบุคคล</a></li>
            <li><a href="<?= base_url('about') ?>" class="mobile-nav__link">เกี่ยวกับ</a></li>
            <li><a href="<?= base_url('contact') ?>" class="mobile-nav__link">ติดต่อ</a></li>
            <li><a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener noreferrer" class="mobile-nav__link">สมัครเรียน</a></li>
            <li class="mobile-nav__divider" role="presentation"></li>
            <?php if (session()->get('admin_logged_in')): ?>
                <li class="mobile-nav__header-item">บัญชีของฉัน</li>
                <li><a href="<?= base_url('dashboard') ?>" class="mobile-nav__link" style="padding-left: 2rem;"><?= esc(session()->get('admin_name') ?: session()->get('admin_email')) ?> (Dashboard)</a></li>
                <li><a href="<?= base_url('admin/logout') ?>" class="mobile-nav__link" style="padding-left: 2rem;">ออกจากระบบ</a></li>
            <?php else: ?>
                <li><a href="<?= base_url('admin/login') ?>" class="mobile-nav__link">เข้าสู่ระบบ</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Search Modal -->
    <div class="search-modal">
        <div class="search-modal__content">
            <input type="text" class="search-modal__input" placeholder="ค้นหา…">
        </div>
    </div>

    <!-- Main Content -->
    <main id="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer__main">
                <!-- Brand -->
                <div class="footer__brand">
                    <div class="footer__logo">
                        <div class="footer__logo-icon"></div>
                        <span class="footer__logo-text"><?= esc($settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?></span>
                    </div>
                    <p class="footer__description">
                        <?= esc($settings['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?><br>
                        สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น
                    </p>
                    <div class="footer__social">
                        <?php if (!empty($settings['facebook'])): ?>
                            <a href="<?= esc($settings['facebook']) ?>" class="footer__social-link" aria-label="Facebook" target="_blank">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Programs -->
                <div class="footer__column">
                    <h4>หลักสูตร</h4>
                    <ul class="footer__links">
                        <li><a href="<?= base_url('academics#bachelor') ?>" class="footer__link">ปริญญาตรี</a></li>
                        <li><a href="<?= base_url('academics#master') ?>" class="footer__link">ปริญญาโท</a></li>
                        <li><a href="<?= base_url('academics#doctorate') ?>" class="footer__link">ปริญญาเอก</a></li>
                    </ul>
                </div>

                <!-- Departments -->
                <div class="footer__column">
                    <h4>สาขาวิชา</h4>
                    <ul class="footer__links">
                        <li><a href="#" class="footer__link">คณิตศาสตร์ประยุกต์</a></li>
                        <li><a href="#" class="footer__link">วิทยาการคอมพิวเตอร์</a></li>
                        <li><a href="#" class="footer__link">เทคโนโลยีสารสนเทศ</a></li>
                        <li><a href="#" class="footer__link">ชีววิทยา</a></li>
                        <li><a href="#" class="footer__link">เคมี</a></li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div class="footer__column">
                    <h4>ลิงก์ด่วน</h4>
                    <ul class="footer__links">
                        <li><a href="<?= base_url('news') ?>" class="footer__link">ข่าวประชาสัมพันธ์</a></li>
                        <li><a href="<?= base_url('research') ?>" class="footer__link">งานวิจัย</a></li>
                        <li><a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener noreferrer" class="footer__link">สมัครเรียน</a></li>
                        <li><a href="<?= base_url('contact') ?>" class="footer__link">ติดต่อเรา</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer__column">
                    <h4>ติดต่อ</h4>
                    <ul class="footer__links">
                        <li style="color: var(--text-light);">
                            <?= esc($settings['address_th'] ?? '27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000') ?>
                        </li>
                        <li style="color: var(--text-light); margin-top: 0.5rem;">
                            โทร: <?= esc($settings['phone'] ?? '055-411096') ?>
                        </li>
                        <li style="color: var(--text-light);">
                            อีเมล: <?= esc($settings['email'] ?? 'sci@uru.ac.th') ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer__bottom">
                <p>&copy; <?= date('Y') + 543 ?> <?= esc($settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?> <?= esc($settings['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?></p>
                <nav class="footer__legal">
                    <a href="#">นโยบายความเป็นส่วนตัว</a>
                    <a href="#">เงื่อนไขการใช้งาน</a>
                </nav>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- อัปเดต Resolution เมื่อ resize / orientation / visualViewport (มือถือ) -->
    <script>
        (function() {
            var body = document.body;

            function applyResolution() {
                var w = window.innerWidth;
                var h = window.innerHeight;
                var dpr = window.devicePixelRatio || 1;
                if (typeof window.visualViewport !== 'undefined') {
                    w = window.visualViewport.width;
                    h = window.visualViewport.height;
                }
                var sizeClasses = ['is-small-phone', 'is-phone', 'is-phablet', 'is-tablet', 'is-laptop', 'is-desktop', 'is-large-desktop', 'is-mobile', 'is-desktop-view', 'is-landscape', 'is-portrait'];
                sizeClasses.forEach(function(c) {
                    body.classList.remove(c);
                });
                if (w < 480) body.classList.add('is-small-phone');
                else if (w < 640) body.classList.add('is-phone');
                else if (w < 768) body.classList.add('is-phablet');
                else if (w < 1024) body.classList.add('is-tablet');
                else if (w < 1200) body.classList.add('is-laptop');
                else if (w < 1440) body.classList.add('is-desktop');
                else body.classList.add('is-large-desktop');
                body.classList.add(w < 768 ? 'is-mobile' : 'is-desktop-view');
                body.classList.add(w > h ? 'is-landscape' : 'is-portrait');
                document.documentElement.style.setProperty('--device-width', w + 'px');
                document.documentElement.style.setProperty('--device-height', h + 'px');
                document.documentElement.style.setProperty('--device-dpr', String(dpr));
                document.documentElement.style.setProperty('--screen-width', (window.screen && window.screen.width ? window.screen.width : w) + 'px');
                document.documentElement.style.setProperty('--screen-height', (window.screen && window.screen.height ? window.screen.height : h) + 'px');
            }
            var timer;

            function debouncedApply() {
                clearTimeout(timer);
                timer = setTimeout(applyResolution, 100);
            }
            window.addEventListener('resize', debouncedApply);
            window.addEventListener('orientationchange', function() {
                setTimeout(applyResolution, 150);
            });
            if (typeof window.visualViewport !== 'undefined') {
                window.visualViewport.addEventListener('resize', debouncedApply);
                window.visualViewport.addEventListener('scroll', debouncedApply);
            }
        })();
    </script>

    <!-- jQuery (Local) -->
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

    <!-- Base URL for API -->
    <script>
        window.BASE_URL = '<?= base_url() ?>';
    </script>

    <!-- University API Module -->
    <script src="<?= base_url('assets/js/api.js') ?>"></script>

    <!-- Main Scripts -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <!-- Silent Auto-Login Iframes (trigger login on other apps) -->
    <?php if (session()->getFlashdata('sso_autologin_urls')): ?>
        <div style="width:0;height:0;overflow:hidden;position:absolute;">
            <?php foreach (session()->getFlashdata('sso_autologin_urls') as $ssoUrl): ?>
                <iframe src="<?= esc($ssoUrl) ?>" style="width:0;height:0;border:0;"></iframe>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- SPA Logic -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.swalAlert = function(msg, type) {
            type = type || 'info';
            var icon = { success: 'success', error: 'error', warning: 'warning', info: 'info' }[type] || 'info';
            return (typeof Swal !== 'undefined') ? Swal.fire({ icon: icon, title: type === 'error' ? 'เกิดข้อผิดพลาด' : (type === 'success' ? 'สำเร็จ' : ''), text: msg }) : Promise.resolve(alert(msg));
        };
        window.swalConfirm = function(opts) {
            var title = (typeof opts === 'string') ? opts : (opts.title || 'ยืนยัน');
            var text = (typeof opts === 'object' && opts.text) ? opts.text : '';
            var confirmText = (typeof opts === 'object' && opts.confirmText) ? opts.confirmText : 'ตกลง';
            var cancelText = (typeof opts === 'object' && opts.cancelText) ? opts.cancelText : 'ยกเลิก';
            if (typeof Swal === 'undefined') return Promise.resolve(window.confirm(title + (text ? '\n' + text : '')));
            return Swal.fire({ title: title, text: text, icon: 'question', showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: cancelText, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d' }).then(function(r) { return r.isConfirmed; });
        };
    </script>
    <?= $this->renderSection('footer_scripts') ?>
</body>

</html>