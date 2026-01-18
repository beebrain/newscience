<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $meta_description ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์' ?>">
    <title><?= $page_title ?? 'คณะวิทยาศาสตร์และเทคโนโลยี' ?> | มหาวิทยาลัยราชภัฏอุตรดิตถ์</title>
    
    <!-- Google Fonts - Thai Support -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    
    <style>
        /* Thai Font Override */
        body {
            font-family: 'Sarabun', 'Noto Sans Thai', sans-serif;
        }
        
        .hero__title, h1, h2, h3, h4, h5, h6 {
            font-family: 'Noto Sans Thai', 'Sarabun', sans-serif;
        }
        
        /* Science Theme Colors */
        :root {
            --primary: #1e3a5f;
            --primary-dark: #0f2744;
            --secondary: #2d7d46;
            --accent: #f5a623;
            --accent-hover: #e09000;
        }
        
        .hero--science {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .hero--science::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1920&h=1080&fit=crop') center/cover;
            opacity: 0.15;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <nav class="top-bar__links">
                <a href="#" class="top-bar__link">นักศึกษา</a>
                <a href="#" class="top-bar__link">บุคลากร</a>
                <a href="#" class="top-bar__link">ศิษย์เก่า</a>
                <a href="<?= base_url('admin/login') ?>" class="top-bar__link">เข้าสู่ระบบ</a>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <!-- Logo -->
            <a href="<?= base_url() ?>" class="logo">
                <?php 
                $logo = $settings['logo'] ?? '';
                if (!empty($logo)): 
                ?>
                <img src="<?= esc($logo) ?>" alt="Logo" class="logo__img" style="height: 50px; width: auto;">
                <?php else: ?>
                <div class="logo__icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <?php endif; ?>
                <div class="logo__text">
                    <?= esc($settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?>
                    <span><?= esc($settings['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?></span>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="nav">
                <ul class="nav__list">
                    <li><a href="<?= base_url() ?>" class="nav__link <?= ($active_page ?? '') === 'home' ? 'active' : '' ?>">หน้าแรก</a></li>
                    <li><a href="<?= base_url('academics') ?>" class="nav__link <?= ($active_page ?? '') === 'academics' ? 'active' : '' ?>">หลักสูตร</a></li>
                    <li><a href="<?= base_url('research') ?>" class="nav__link <?= ($active_page ?? '') === 'research' ? 'active' : '' ?>">วิจัย</a></li>
                    <li><a href="<?= base_url('news') ?>" class="nav__link <?= ($active_page ?? '') === 'news' ? 'active' : '' ?>">ข่าว</a></li>
                    <li><a href="<?= base_url('about') ?>" class="nav__link <?= ($active_page ?? '') === 'about' ? 'active' : '' ?>">เกี่ยวกับ</a></li>
                    <li><a href="<?= base_url('contact') ?>" class="nav__link <?= ($active_page ?? '') === 'contact' ? 'active' : '' ?>">ติดต่อ</a></li>
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
                <div class="logo__icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
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
            <li><a href="<?= base_url() ?>" class="mobile-nav__link">หน้าแรก</a></li>
            <li><a href="<?= base_url('academics') ?>" class="mobile-nav__link">หลักสูตร</a></li>
            <li><a href="<?= base_url('research') ?>" class="mobile-nav__link">วิจัย</a></li>
            <li><a href="<?= base_url('news') ?>" class="mobile-nav__link">ข่าว</a></li>
            <li><a href="<?= base_url('about') ?>" class="mobile-nav__link">เกี่ยวกับ</a></li>
            <li><a href="<?= base_url('contact') ?>" class="mobile-nav__link">ติดต่อ</a></li>
            <li><a href="<?= base_url('admission') ?>" class="mobile-nav__link">สมัครเรียน</a></li>
        </ul>
    </div>

    <!-- Search Modal -->
    <div class="search-modal">
        <div class="search-modal__content">
            <input type="text" class="search-modal__input" placeholder="ค้นหา...">
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
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Programs -->
                <div class="footer__column">
                    <h4>หลักสูตร</h4>
                    <ul class="footer__links">
                        <li><a href="<?= base_url('academics') ?>" class="footer__link">ปริญญาตรี</a></li>
                        <li><a href="<?= base_url('academics') ?>" class="footer__link">ปริญญาโท</a></li>
                        <li><a href="<?= base_url('academics') ?>" class="footer__link">ปริญญาเอก</a></li>
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
                        <li><a href="<?= base_url('admission') ?>" class="footer__link">สมัครเรียน</a></li>
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
    <!-- jQuery 3.7.1 (latest stable) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Base URL for API -->
    <script>
        window.BASE_URL = '<?= base_url() ?>';
    </script>
    
    <!-- University API Module -->
    <script src="<?= base_url('assets/js/api.js') ?>"></script>
    
    <!-- Main Scripts -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
