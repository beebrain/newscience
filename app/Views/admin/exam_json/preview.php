<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>ตรวจสอบตารางคุมสอบ</h2>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">
                    <?= esc($metadata['semester'] ?? '') ?> -
                    <?= ($metadata['exam_type'] ?? '') === 'midterm' ? 'กลางภาค' : 'ปลายภาค' ?>
                </p>
            </div>
            <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                <a href="<?= base_url('admin/exam') ?>" class="btn" style="background: var(--color-gray-200);">กลับ</a>
                <?php if (!$is_published): ?>
                    <button onclick="publishSchedule()" class="btn btn-success">เผยแพร่</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Stats -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div class="stat-card" style="background: var(--color-gray-50); padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-primary);"><?= number_format($stats['total_schedules'] ?? 0) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-gray-600);">รายการสอบ</div>
            </div>
            <div class="stat-card" style="background: var(--color-gray-50); padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-primary);"><?= number_format($stats['total_courses'] ?? 0) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-gray-600);">รายวิชา</div>
            </div>
            <div class="stat-card" style="background: var(--color-gray-50); padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-primary);"><?= number_format($stats['total_examiners'] ?? 0) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-gray-600);">ผู้คุมสอบ</div>
            </div>
        </div>

        <!-- Matching Analysis -->
        <?php if (isset($stats['matching_analysis'])): ?>
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-800);">📊 การจับคู่ผู้คุมสอบกับผู้ใช้ระบบ</h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div class="stat-card" style="background: var(--color-gray-50); padding: 1rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-primary);"><?= number_format($stats['matching_analysis']['total_unique_examiners'] ?? 0) ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-600);">ผู้คุมสอบทั้งหมด</div>
                    </div>
                    <div class="stat-card" style="background: rgba(var(--color-success-rgb), 0.1); padding: 1rem; border-radius: 8px; text-align: center; border: 1px solid var(--color-success);">
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-success);"><?= number_format($stats['matching_analysis']['matched_examiners'] ?? 0) ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-600);">Match ได้</div>
                    </div>
                    <div class="stat-card" style="background: rgba(var(--color-danger-rgb), 0.1); padding: 1rem; border-radius: 8px; text-align: center; border: 1px solid var(--color-danger);">
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-danger);"><?= number_format($stats['matching_analysis']['unmatched_examiners'] ?? 0) ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-600);">ไม่ Match</div>
                    </div>
                    <div class="stat-card" style="background: var(--color-gray-50); padding: 1rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: <?= ($stats['matching_analysis']['match_rate'] ?? 0) >= 70 ? 'var(--color-success)' : (($stats['matching_analysis']['match_rate'] ?? 0) >= 50 ? 'var(--color-warning)' : 'var(--color-danger)') ?>;"><?= number_format($stats['matching_analysis']['match_rate'] ?? 0) ?>%</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-600);">Match Rate</div>
                    </div>
                </div>

                <?php if (!empty($stats['matching_analysis']['unmatched_list'])): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: rgba(var(--color-danger-rgb), 0.05); border: 1px solid var(--color-danger); border-radius: 8px;">
                        <h4 style="font-size: 0.875rem; color: var(--color-danger); margin-bottom: 0.5rem;">⚠️ รายชื่อผู้คุมสอบที่ไม่ Match (<?= count($stats['matching_analysis']['unmatched_list']) ?> คน):</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php foreach ($stats['matching_analysis']['unmatched_list'] as $unmatched): ?>
                                <span style="background: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;"><?= esc($unmatched) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- User View Preview Section -->
        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--color-gray-300);">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--color-gray-800);">👁️ มุมมองของผู้ใช้ (User Preview)</h3>

            <!-- Summary Cards -->
            <div class="summary-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div class="summary-card" style="background: var(--color-gray-50); padding: 1.25rem; border-radius: 12px; text-align: center; border: 1px solid var(--color-gray-200);">
                    <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;"><?= number_format($stats['total_schedules'] ?? 0) ?></div>
                    <div style="font-size: 0.875rem; color: var(--color-gray-600);">ตารางทั้งหมด</div>
                </div>
                <div class="summary-card" style="background: rgba(var(--color-primary-rgb), 0.05); padding: 1.25rem; border-radius: 12px; text-align: center; border: 1px solid var(--color-primary);">
                    <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">-</div>
                    <div style="font-size: 0.875rem; color: var(--color-gray-600);">ตารางของฉัน</div>
                    <div style="font-size: 0.75rem; color: var(--color-gray-500); margin-top: 0.25rem;">(จำลองตามผู้ใช้)</div>
                </div>
                <div class="summary-card" style="background: var(--color-gray-50); padding: 1.25rem; border-radius: 12px; text-align: center; border: 1px solid var(--color-gray-200);">
                    <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;"><?= number_format($stats['total_examiners'] ?? 0) ?></div>
                    <div style="font-size: 0.875rem; color: var(--color-gray-600);">อาจารย์ทั้งหมด</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs-header" style="display: flex; border-bottom: 2px solid var(--color-gray-200); margin-bottom: 1rem;">
                    <button class="tab-button active" data-tab="all" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid var(--color-primary); color: var(--color-primary); font-weight: 600; cursor: pointer;">
                        ตารางรวมทั้งหมด
                    </button>
                    <button class="tab-button" data-tab="mine" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: var(--color-gray-600); font-weight: 600; cursor: pointer;">
                        ตารางของฉัน
                    </button>
                    <button class="tab-button" data-tab="instructors" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: var(--color-gray-600); font-weight: 600; cursor: pointer;">
                        อาจารย์อื่นๆ
                    </button>
                    <button class="tab-button" data-tab="instructor-detail" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: var(--color-gray-600); font-weight: 600; cursor: pointer;">
                        ตารางอาจารย์
                    </button>
                </div>

                <!-- Tab Panels -->
                <div class="tab-panels">
                    <!-- All Schedules Tab -->
                    <div class="tab-panel active" id="panel-all">
                        <div class="table-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; overflow: hidden;">
                            <div class="table-wrap" style="overflow-x: auto;">
                                <table class="exam-table" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: var(--color-gray-50);">
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">วันที่</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">เวลา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">รหัสวิชา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ชื่อวิชา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">กลุ่ม</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ห้อง</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">อาจารย์เจ้าของรายวิชา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ผู้คุมสอบ 1</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ผู้คุมสอบ 2</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">บทบาท</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($schedules)): ?>
                                            <tr>
                                                <td colspan="10" style="text-align: center; padding: 2rem; color: var(--color-gray-600);">ไม่พบข้อมูล</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $unmatchedList = $stats['matching_analysis']['unmatched_list'] ?? [];
                                            $courseOwners = $stats['course_owners'] ?? [];
                                            foreach ($schedules as $schedule):
                                                $examiner1 = $schedule['examiner1'] ?? '';
                                                $examiner2 = $schedule['examiner2'] ?? '';
                                                $examiner1Unmatched = in_array($examiner1, $unmatchedList);
                                                $examiner2Unmatched = in_array($examiner2, $unmatchedList);

                                                $courseCode = $schedule['course_code'] ?? '';
                                                $courseOwnerInfo = $courseOwners[$courseCode] ?? ['owner_info' => ['matched' => false, 'user_info' => null]];
                                                $ownerMatched = $courseOwnerInfo['owner_info']['matched'] ?? false;
                                                $ownerInfo = $courseOwnerInfo['owner_info']['user_info'] ?? null;

                                                // Determine roles for current user (simulation)
                                                $roles = [];
                                                if ($ownerMatched) $roles[] = 'เจ้าของรายวิชา';
                                                if (!$examiner1Unmatched && $examiner1) $roles[] = 'ผู้คุมสอบ 1';
                                                if (!$examiner2Unmatched && $examiner2) $roles[] = 'ผู้คุมสอบ 2';
                                            ?>
                                                <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                                    <td style="padding: 1rem; font-size: 0.875rem;"><?= esc($schedule['exam_date'] ?? '-') ?></td>
                                                    <td style="padding: 1rem; font-size: 0.875rem;"><?= esc($schedule['exam_time'] ?? '-') ?></td>
                                                    <td style="padding: 1rem; font-size: 0.875rem; font-weight: 600;"><?= esc($schedule['course_code'] ?? '-') ?></td>
                                                    <td style="padding: 1rem; font-size: 0.875rem;"><?= esc($schedule['course_name'] ?? '-') ?></td>
                                                    <td style="padding: 1rem; font-size: 0.875rem;"><?= esc($schedule['student_group'] ?? '-') ?></td>
                                                    <td style="padding: 1rem; font-size: 0.875rem;"><?= esc($schedule['room'] ?? '-') ?></td>
                                                    <td style="<?= $ownerMatched ? 'background: rgba(var(--color-success-rgb), 0.1);' : 'background: rgba(var(--color-warning-rgb), 0.1);' ?>">
                                                        <?php if ($ownerMatched && $ownerInfo): ?>
                                                            <div style="font-weight: 600; color: var(--color-success);">
                                                                <?= esc($ownerInfo['nickname'] ?: $ownerInfo['thai_name']) ?>
                                                                <span style="font-size: 0.7rem; margin-left: 0.25rem;">✓</span>
                                                            </div>
                                                            <div style="font-size: 0.7rem; color: var(--color-gray-600);">
                                                                (<?= esc($ownerInfo['login_uid']) ?>)
                                                            </div>
                                                        <?php else: ?>
                                                            <div style="color: var(--color-warning);">
                                                                <?= esc($schedule['instructor']) ?>
                                                                <span style="font-size: 0.7rem; margin-left: 0.25rem;">⚠️</span>
                                                            </div>
                                                            <div style="font-size: 0.7rem; color: var(--color-gray-600);">
                                                                (ไม่ match)
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="<?= $examiner1Unmatched ? 'background: rgba(var(--color-danger-rgb), 0.1); color: var(--color-danger); font-weight: 600;' : '' ?>">
                                                        <?= esc($examiner1) ?>
                                                        <?php if ($examiner1Unmatched): ?>
                                                            <span style="font-size: 0.7rem; margin-left: 0.25rem;">⚠️</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="<?= $examiner2Unmatched ? 'background: rgba(var(--color-danger-rgb), 0.1); color: var(--color-danger); font-weight: 600;' : '' ?>">
                                                        <?= esc($examiner2) ?>
                                                        <?php if ($examiner2Unmatched): ?>
                                                            <span style="font-size: 0.7rem; margin-left: 0.25rem;">⚠️</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($roles)): ?>
                                                            <?php foreach ($roles as $role): ?>
                                                                <span style="background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; margin-right: 0.25rem;">
                                                                    <?= esc($role) ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span style="color: var(--color-gray-500); font-size: 0.75rem;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- My Schedules Tab -->
                    <div class="tab-panel" id="panel-mine" style="display: none;">
                        <div class="table-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; overflow: hidden;">
                            <div class="table-wrap" style="overflow-x: auto;">
                                <table class="exam-table" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: var(--color-gray-50);">
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">วันที่</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">เวลา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">รหัสวิชา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ชื่อวิชา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">กลุ่ม</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ห้อง</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">อาจารย์เจ้าของรายวิชา</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ผู้คุมสอบ 1</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ผู้คุมสอบ 2</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">บทบาท</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="10" style="text-align: center; padding: 2rem; color: var(--color-gray-600);">
                                                <div style="background: var(--color-gray-50); padding: 1rem; border-radius: 8px; border: 1px dashed var(--color-gray-300);">
                                                    <div style="font-size: 0.875rem; margin-bottom: 0.5rem;">📋 จำลองตารางของผู้ใช้</div>
                                                    <div style="font-size: 0.75rem; color: var(--color-gray-500);">
                                                        หน้านี้จะแสดงเฉพาะตารางที่ผู้ใช้เป็นผู้คุมสอบหรือเจ้าของรายวิชา<br>
                                                        ข้อมูลจะแตกต่างกันไปตามผู้ใช้แต่ละคน
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Other Instructors Tab -->
                    <div class="tab-panel" id="panel-instructors" style="display: none;">
                        <div class="exam-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; padding: 1.5rem;">
                            <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                                <?php
                                // Extract unique instructors from schedules
                                $instructors = [];
                                $instructorMap = [];

                                foreach ($schedules as $schedule) {
                                    $examiner1 = trim($schedule['examiner1'] ?? '');
                                    $examiner2 = trim($schedule['examiner2'] ?? '');
                                    $instructor = trim($schedule['instructor'] ?? '');

                                    // Add examiners
                                    foreach ([$examiner1, $examiner2] as $name) {
                                        if ($name && !isset($instructorMap[$name])) {
                                            $instructorMap[$name] = [
                                                'name' => $name,
                                                'exam_count' => 0,
                                                'owner_count' => 0,
                                                'exam_schedules' => [],
                                                'owner_courses' => []
                                            ];
                                        }
                                        if ($name) {
                                            $instructorMap[$name]['exam_count']++;
                                            $instructorMap[$name]['exam_schedules'][] = $schedule;
                                        }
                                    }

                                    // Add course owners
                                    if ($instructor && !isset($instructorMap[$instructor])) {
                                        $instructorMap[$instructor] = [
                                            'name' => $instructor,
                                            'exam_count' => 0,
                                            'owner_count' => 0,
                                            'exam_schedules' => [],
                                            'owner_courses' => []
                                        ];
                                    }
                                    if ($instructor) {
                                        $instructorMap[$instructor]['owner_count']++;
                                        $instructorMap[$instructor]['owner_courses'][] = $schedule;
                                    }
                                }

                                $instructors = array_values($instructorMap);
                                usort($instructors, function ($a, $b) {
                                    return strcmp($a['name'], $b['name']);
                                });
                                ?>

                                <?php if (empty($instructors)): ?>
                                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--color-gray-600);">
                                        ไม่พบข้อมูลอาจารย์
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <div class="instructor-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; padding: 1.25rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            <div class="instructor-card__name" style="font-size: 1.125rem; font-weight: 600; color: var(--color-gray-800); margin-bottom: 0.75rem;">
                                                <?= esc($instructor['name']) ?>
                                            </div>
                                            <div class="instructor-card__stats" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                                                <span class="instructor-card__stat" style="background: rgba(var(--color-primary-rgb), 0.1); border-radius: 999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; color: var(--color-primary);">
                                                    คุม <?= $instructor['exam_count'] ?>
                                                </span>
                                                <?php if ($instructor['owner_count'] > 0): ?>
                                                    <span class="instructor-card__stat" style="background: rgba(var(--color-success-rgb), 0.1); border-radius: 999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; color: var(--color-success);">
                                                        เจ้าของ <?= $instructor['owner_count'] ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Instructor Detail Tab -->
                    <div class="tab-panel" id="panel-instructor-detail" style="display: none;">
                        <div style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; padding: 1.5rem;">
                            <!-- Instructor Selector -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--color-gray-800);">เลือกอาจารย์:</label>
                                <select id="instructorSelector" style="width: 100%; max-width: 300px; padding: 0.5rem; border: 1px solid var(--color-gray-300); border-radius: 6px; font-size: 0.875rem;">
                                    <option value="">-- เลือกอาจารย์ --</option>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?= esc($instructor['name']) ?>"><?= esc($instructor['name']) ?> (คุม <?= $instructor['exam_count'] ?>, เจ้าของ <?= $instructor['owner_count'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Instructor Details -->
                            <div id="instructorDetails" style="display: none;">
                                <!-- Exam Schedules Table -->
                                <div style="margin-bottom: 2rem;">
                                    <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-800);">📋 ตารางคุมสอบ</h4>
                                    <div class="table-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; overflow: hidden;">
                                        <div class="table-wrap" style="overflow-x: auto;">
                                            <table class="exam-table" style="width: 100%; border-collapse: collapse;">
                                                <thead>
                                                    <tr style="background: var(--color-gray-50);">
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">วันที่</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">เวลา</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">รหัสวิชา</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ชื่อวิชา</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">กลุ่ม</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ห้อง</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">บทบาท</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="instructorExamTableBody">
                                                    <tr>
                                                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--color-gray-600);">กรุณาเลือกอาจารย์</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Owned Courses Table -->
                                <div>
                                    <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-800);">📚 รายวิชาที่เป็นเจ้าของ</h4>
                                    <div class="table-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; overflow: hidden;">
                                        <div class="table-wrap" style="overflow-x: auto;">
                                            <table class="exam-table" style="width: 100%; border-collapse: collapse;">
                                                <thead>
                                                    <tr style="background: var(--color-gray-50);">
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">รหัสวิชา</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ชื่อวิชา</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">กลุ่ม</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">วันที่สอบ</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">เวลาสอบ</th>
                                                        <th style="padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem;">ห้องสอบ</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="instructorCoursesTableBody">
                                                    <tr>
                                                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-gray-600);">กรุณาเลือกอาจารย์</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div style="margin-bottom: 1rem;">
            <?php if ($is_published): ?>
                <span class="badge badge-success">เผยแพร่แล้ว</span>
                <?php if (!empty($metadata['published_at'])): ?>
                    <span style="font-size: 0.75rem; color: var(--color-gray-600); margin-left: 0.5rem;">
                        เมื่อ <?= date('d/m/Y H:i', strtotime($metadata['published_at'])) ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge" style="background: var(--color-warning);">ร่าง</span>
                <span style="font-size: 0.75rem; color: var(--color-gray-600); margin-left: 0.5rem;">ยังไม่ได้เผยแพร่</span>
            <?php endif; ?>
        </div>

        <!-- File Info -->
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--color-gray-200);">
            <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">ข้อมูลไฟล์:</h4>
            <table style="font-size: 0.75rem;">
                <tr>
                    <td style="padding: 2px 8px 2px 0;"><strong>ไฟล์:</strong></td>
                    <td><?= esc($filename) ?></td>
                </tr>
                <tr>
                    <td style="padding: 2px 8px 2px 0;"><strong>นำเข้าเมื่อ:</strong></td>
                    <td><?= !empty($metadata['uploaded_at']) ? date('d/m/Y H:i', strtotime($metadata['uploaded_at'])) : '-' ?></td>
                </tr>
                <tr>
                    <td style="padding: 2px 8px 2px 0;"><strong>ไฟล์ต้นฉบับ:</strong></td>
                    <td><?= esc($metadata['filename'] ?? '-') ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-success {
        background: var(--color-success);
        color: white;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-success {
        background: var(--color-success);
        color: white;
    }

    .table th {
        background: var(--color-gray-50);
        font-weight: 600;
        font-size: 0.8rem;
    }

    .table td {
        font-size: 0.8rem;
        padding: 0.5rem;
    }
</style>

<script>
    // Store instructor data for JavaScript
    const instructorData = <?= json_encode($instructors) ?>;

    function publishSchedule() {
        if (!confirm('ยืนยันการเผยแพร่ตารางสอบ? หลังเผยแพร่ผู้ใช้จะสามารถเห็นข้อมูลได้')) return;

        const url = window.location.href;
        const parts = url.split('/');
        const semesterNo = parts[parts.length - 3];
        const year = parts[parts.length - 2];
        const examType = parts[parts.length - 1];

        fetch(`<?= base_url('admin/exam/publish/') ?>${semesterNo}/${year}/${examType}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('เผยแพร่สำเร็จ');
                    location.reload();
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาด');
            });
    }

    // Tab switching for user preview
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanels = document.querySelectorAll('.tab-panel');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.dataset.tab;

                // Remove active class from all buttons and panels
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.borderBottomColor = 'transparent';
                    btn.style.color = 'var(--color-gray-600)';
                });
                tabPanels.forEach(panel => {
                    panel.classList.remove('active');
                    panel.style.display = 'none';
                });

                // Add active class to clicked button and corresponding panel
                button.classList.add('active');
                button.style.borderBottomColor = 'var(--color-primary)';
                button.style.color = 'var(--color-primary)';

                const targetPanel = document.getElementById(`panel-${targetTab}`);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                    targetPanel.style.display = 'block';
                }
            });
        });

        // Add hover effect for instructor cards
        const instructorCards = document.querySelectorAll('.instructor-card');
        instructorCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.borderColor = 'var(--color-primary)';
                card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                card.style.transform = 'translateY(-2px)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.borderColor = 'var(--color-gray-200)';
                card.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
                card.style.transform = 'translateY(0)';
            });
        });

        // Instructor selector functionality
        const instructorSelector = document.getElementById('instructorSelector');
        const instructorDetails = document.getElementById('instructorDetails');
        const examTableBody = document.getElementById('instructorExamTableBody');
        const coursesTableBody = document.getElementById('instructorCoursesTableBody');

        if (instructorSelector) {
            instructorSelector.addEventListener('change', (e) => {
                const selectedInstructorName = e.target.value;

                if (!selectedInstructorName) {
                    instructorDetails.style.display = 'none';
                    return;
                }

                // Find instructor data
                const instructor = instructorData.find(inst => inst.name === selectedInstructorName);

                if (!instructor) {
                    instructorDetails.style.display = 'none';
                    return;
                }

                // Show instructor details
                instructorDetails.style.display = 'block';

                // Populate exam schedules table
                if (instructor.exam_schedules && instructor.exam_schedules.length > 0) {
                    examTableBody.innerHTML = instructor.exam_schedules.map(schedule => {
                        // Determine role
                        let role = 'ผู้คุมสอบ';
                        if (schedule.examiner1 === selectedInstructorName) {
                            role = 'ผู้คุมสอบ 1';
                        } else if (schedule.examiner2 === selectedInstructorName) {
                            role = 'ผู้คุมสอบ 2';
                        }

                        return `
                            <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                <td style="padding: 1rem; font-size: 0.875rem;">${schedule.exam_date || '-'}</td>
                                <td style="padding: 1rem; font-size: 0.875rem;">${schedule.exam_time || '-'}</td>
                                <td style="padding: 1rem; font-size: 0.875rem; font-weight: 600;">${schedule.course_code || '-'}</td>
                                <td style="padding: 1rem; font-size: 0.875rem;">${schedule.course_name || '-'}</td>
                                <td style="padding: 1rem; font-size: 0.875rem;">${schedule.student_group || '-'}</td>
                                <td style="padding: 1rem; font-size: 0.875rem;">${schedule.room || '-'}</td>
                                <td style="padding: 1rem; font-size: 0.875rem;">
                                    <span style="background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">
                                        ${role}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    examTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--color-gray-600);">ไม่พบตารางคุมสอบ</td></tr>';
                }

                // Populate owned courses table
                if (instructor.owner_courses && instructor.owner_courses.length > 0) {
                    coursesTableBody.innerHTML = instructor.owner_courses.map(course => `
                        <tr style="border-bottom: 1px solid var(--color-gray-100);">
                            <td style="padding: 1rem; font-size: 0.875rem; font-weight: 600;">${course.course_code || '-'}</td>
                            <td style="padding: 1rem; font-size: 0.875rem;">${course.course_name || '-'}</td>
                            <td style="padding: 1rem; font-size: 0.875rem;">${course.student_group || '-'}</td>
                            <td style="padding: 1rem; font-size: 0.875rem;">${course.exam_date || '-'}</td>
                            <td style="padding: 1rem; font-size: 0.875rem;">${course.exam_time || '-'}</td>
                            <td style="padding: 1rem; font-size: 0.875rem;">${course.room || '-'}</td>
                        </tr>
                    `).join('');
                } else {
                    coursesTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-gray-600);">ไม่พบรายวิชาที่เป็นเจ้าของ</td></tr>';
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>