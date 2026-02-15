<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<?php if (!$program): ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 3rem;">
            <h2>ไม่พบหลักสูตร</h2>
            <p>หลักสูตรที่คุณค้นหาไม่มีอยู่ในระบบ</p>
            <a href="<?= base_url('academics') ?>" class="btn btn-primary">กลับไปหน้าหลักสูตร</a>
        </div>
    </div>
<?php else: ?>
    <!-- Hero Section -->
    <section class="program-hero" style="background: linear-gradient(135deg, <?= $page['theme_color'] ?? '#1e40af' ?> 0%, <?= $page['theme_color'] ?? '#1e40af' ?>dd 100%); color: white; padding: 4rem 0; margin-bottom: 3rem;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="program-title" style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;"><?= esc($program['name_th']) ?></h1>
                    <p class="program-degree" style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;"><?= esc($program['degree_th']) ?></p>
                    <div class="program-stats" style="display: flex; gap: 2rem; flex-wrap: wrap;">
                        <div class="stat-item" style="text-align: center;">
                            <div class="stat-number" style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= esc($program['credits'] ?? '4') ?></div>
                            <div class="stat-label" style="font-size: 0.875rem; opacity: 0.8;">หน่วยกิต</div>
                        </div>
                        <div class="stat-item" style="text-align: center;">
                            <div class="stat-number" style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= esc($program['duration'] ?? '4') ?></div>
                            <div class="stat-label" style="font-size: 0.875rem; opacity: 0.8;">ปี</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php if (!empty($program['image'])): ?>
                        <img src="<?= base_url('serve/uploads/programs/' . $program['image']) ?>" alt="<?= esc($program['name_th']) ?>" class="program-image" style="width: 100%; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!-- Content Sections -->
                <?php if (!empty($page['philosophy'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">ปรัชญาหลักสูตร</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['philosophy'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['objectives'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">วัตถุประสงค์</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['objectives'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['graduate_profile'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">คุณลักษณะบัณฑิต</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['graduate_profile'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['curriculum_structure'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">โครงสร้างหลักสูตร</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['curriculum_structure'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['study_plan'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">แผนการเรียน</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['study_plan'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['career_prospects'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">อาชีพที่สามารถประกอบได้</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['career_prospects'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['tuition_fees'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">ค่าเล่าเรียน/ค่าธรรมเนียม</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['tuition_fees'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['admission_info'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">การรับสมัคร</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['admission_info'] ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($page['contact_info'])): ?>
                    <section class="content-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">ข้อมูลติดต่อ</h2>
                        <div class="section-content" style="color: var(--color-gray-700); line-height: 1.8; font-size: 1.1rem;"><?= $page['contact_info'] ?></div>
                    </section>
                <?php endif; ?>

                <!-- News Section -->
                <?php if (!empty($news)): ?>
                    <section class="news-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">ข่าวประชาสัมพันธ์</h2>
                        <div class="news-grid" style="display: grid; gap: 1.5rem;">
                            <?php foreach ($news as $newsItem): ?>
                                <div class="news-item" style="border: 1px solid var(--color-gray-200); border-radius: 8px; padding: 1.5rem; transition: box-shadow 0.2s;">
                                    <div class="news-date" style="color: var(--color-gray-500); font-size: 0.875rem; margin-bottom: 0.5rem;"><?= date('d/m/Y', strtotime($newsItem['created_at'])) ?></div>
                                    <h3 class="news-title" style="margin-bottom: 0.5rem;">
                                        <a href="<?= base_url('news/' . $newsItem['id']) ?>" style="color: var(--color-gray-900); text-decoration: none; font-weight: 600;"><?= esc($newsItem['title_th']) ?></a>
                                    </h3>
                                    <p class="news-excerpt" style="color: var(--color-gray-600); margin-bottom: 1rem;"><?= character_limiter(strip_tags($newsItem['content_th']), 150) ?></p>
                                    <a href="<?= base_url('news/' . $newsItem['id']) ?>" class="read-more" style="color: <?= $page['theme_color'] ?? '#1e40af' ?>; text-decoration: none; font-weight: 500;">อ่านต่อ →</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($news) >= 5): ?>
                            <div style="text-align: center; margin-top: 2rem;">
                                <a href="<?= base_url('news?tag=program_' . $program['id']) ?>" class="btn btn-outline">ดูข่าวทั้งหมด</a>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Personnel Section -->
                <?php if (!empty($personnel)): ?>
                    <section class="personnel-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">บุคลากร</h2>
                        <div class="personnel-grid" style="display: grid; gap: 1.5rem;">
                            <?php foreach ($personnel as $person): ?>
                                <div class="personnel-card" style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                    <div class="person-avatar" style="width: 64px; height: 64px; background: var(--color-gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <?php if (!empty($person['image'])): ?>
                                            <img src="<?= base_url('serve/uploads/personnel/' . $person['image']) ?>" alt="<?= esc($person['name']) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                                <circle cx="12" cy="7" r="4" />
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 class="person-name" style="margin: 0; font-weight: 600; color: var(--color-gray-900);"><?= esc($person['name']) ?></h4>
                                        <p class="person-position" style="margin: 0.25rem 0 0 0; color: var(--color-gray-600);"><?= esc($person['position'] ?? '') ?></p>
                                        <?php if (!empty($person['role_in_curriculum'])): ?>
                                            <span class="role-badge" style="display: inline-block; padding: 0.25rem 0.75rem; background: <?= $page['theme_color'] ?? '#1e40af' ?>20; color: <?= $page['theme_color'] ?? '#1e40af' ?>; border-radius: 12px; font-size: 0.75rem; font-weight: 500; margin-top: 0.5rem;"><?= esc($person['role_in_curriculum']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Downloads Section -->
                <?php if (!empty($downloads)): ?>
                    <section class="downloads-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">ดาวน์โหลดเอกสาร</h2>
                        <div class="downloads-list" style="display: grid; gap: 1rem;">
                            <?php foreach ($downloads as $download): ?>
                                <div class="download-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px; transition: box-shadow 0.2s;">
                                    <div class="file-icon" style="width: 48px; height: 48px; background: var(--color-gray-100); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="16" y1="13" x2="8" y2="13" />
                                            <line x1="16" y1="17" x2="8" y2="17" />
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="file-title" style="font-weight: 600; margin-bottom: 0.25rem;"><?= esc($download['title']) ?></div>
                                        <div class="file-info" style="font-size: 0.875rem; color: var(--color-gray-600);">
                                            <span style="text-transform: uppercase; font-weight: 500;"><?= esc($download['file_type']) ?></span> •
                                            <?= $programDownloadModel->getFormattedSize($download['file_size']) ?>
                                        </div>
                                    </div>
                                    <a href="<?= base_url('serve/' . $download['file_path']) ?>" class="btn btn-primary btn-sm" download>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                            <polyline points="7 10 12 15 17 10" />
                                            <line x1="12" y1="15" x2="12" y2="3" />
                                        </svg>
                                        ดาวน์โหลด
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Video Section -->
                <?php if (!empty($page['intro_video_url'])): ?>
                    <section class="video-section" style="margin-bottom: 3rem;">
                        <h2 class="section-title" style="color: var(--color-gray-900); margin-bottom: 1.5rem; font-size: 1.875rem; font-weight: 600; border-bottom: 3px solid <?= $page['theme_color'] ?? '#1e40af' ?>; padding-bottom: 0.5rem;">วิดีโอแนะนำ</h2>
                        <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <?php
                            $videoUrl = $page['intro_video_url'];
                            if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                                // Extract YouTube video ID
                                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $videoUrl, $matches)) {
                                    $videoId = $matches[1];
                                    $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                                    echo "<iframe src='{$embedUrl}' style='position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;' allowfullscreen></iframe>";
                                }
                            } else {
                                echo "<video controls style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'><source src='{$videoUrl}' type='video/mp4'>Your browser does not support the video tag.</video>";
                            }
                            ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="sidebar" style="position: sticky; top: 2rem;">
                    <!-- Quick Info -->
                    <div class="quick-info-card" style="background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                        <h3 style="margin: 0 0 1rem 0; color: var(--color-gray-900); font-size: 1.25rem; font-weight: 600;">ข้อมูลสำคัญ</h3>
                        <div class="info-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div class="info-item">
                                <strong>ระดับ:</strong> <?= esc($program['level']) ?>
                            </div>
                            <div class="info-item">
                                <strong>ปริญญา:</strong> <?= esc($program['degree_th']) ?>
                            </div>
                            <div class="info-item">
                                <strong>หน่วยกิต:</strong> <?= esc($program['credits'] ?? '-') ?>
                            </div>
                            <div class="info-item">
                                <strong>ระยะเวลา:</strong> <?= esc($program['duration'] ?? '-') ?> ปี
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <?php if (!empty($page['contact_info'])): ?>
                        <div class="contact-card" style="background: <?= $page['theme_color'] ?? '#1e40af' ?>10; border: 1px solid <?= $page['theme_color'] ?? '#1e40af' ?>30; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                            <h3 style="margin: 0 0 1rem 0; color: <?= $page['theme_color'] ?? '#1e40af' ?>; font-size: 1.25rem; font-weight: 600;">ติดต่อ</h3>
                            <div class="contact-info" style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['contact_info'] ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Apply Button -->
                    <div class="apply-card" style="text-align: center; margin-bottom: 2rem;">
                        <a href="<?= base_url('admission') ?>" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                                <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="8.5" cy="7" r="4" />
                                <line x1="20" y1="8" x2="20" y2="14" />
                                <line x1="23" y1="11" x2="17" y2="11" />
                            </svg>
                            สมัครเรียน
                        </a>
                    </div>

                    <!-- Related Links -->
                    <div class="links-card" style="background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 12px; padding: 1.5rem;">
                        <h3 style="margin: 0 0 1rem 0; color: var(--color-gray-900); font-size: 1.25rem; font-weight: 600;">ลิงก์ที่เกี่ยวข้อง</h3>
                        <div class="links-list" style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="<?= base_url('academics') ?>" class="link-item" style="color: var(--color-gray-700); text-decoration: none; padding: 0.5rem 0; border-bottom: 1px solid var(--color-gray-200);">→ หลักสูตรทั้งหมด</a>
                            <a href="<?= base_url('admission') ?>" class="link-item" style="color: var(--color-gray-700); text-decoration: none; padding: 0.5rem 0; border-bottom: 1px solid var(--color-gray-200);">→ การรับสมัคร</a>
                            <a href="<?= base_url('contact') ?>" class="link-item" style="color: var(--color-gray-700); text-decoration: none; padding: 0.5rem 0;">→ ติดต่อเรา</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .news-item:hover,
    .download-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .news-grid {
        display: grid;
        gap: 1.5rem;
    }

    @media (min-width: 768px) {
        .news-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    .personnel-grid {
        display: grid;
        gap: 1.5rem;
    }

    @media (min-width: 768px) {
        .personnel-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    .read-more:hover {
        text-decoration: underline;
    }

    .link-item:hover {
        color: <?= $page['theme_color'] ?? '#1e40af' ?>;
    }
</style>

<?= $this->endSection() ?>