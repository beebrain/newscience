<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="preview-header" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
        <div>
            <a href="<?= base_url('admin/exam') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-gray-600); text-decoration: none; margin-bottom: 0.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                กลับไปหน้าจัดการ
            </a>
            <h2 style="margin: 0;">ตรวจสอบข้อมูลนำเข้า</h2>
        </div>
        <?php if ($batch['status'] === 'draft'): ?>
            <button type="button" onclick="publishBatch()" class="btn btn-success" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
                เผยแพร่
            </button>
        <?php endif; ?>
    </div>

    <!-- Info Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="info-card" style="background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">ภาคเรียน</div>
            <div style="font-size: 1.25rem; font-weight: 600;"><?= esc($batch['semester_label']) ?></div>
        </div>
        <div class="info-card" style="background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">ประเภท</div>
            <div style="font-size: 1.25rem; font-weight: 600;">
                <?= $batch['exam_type'] === 'midterm' ? 'สอบกลางภาค' : 'สอบปลายภาค' ?>
            </div>
        </div>
        <div class="info-card" style="background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">จำนวนรายการ</div>
            <div style="font-size: 1.25rem; font-weight: 600;"><?= count($schedules) ?></div>
        </div>
        <div class="info-card" style="background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">จับคู่แล้ว</div>
            <div style="font-size: 1.25rem; font-weight: 600; color: var(--color-success);">
                <?= $matchStats['matched'] ?> / <?= $matchStats['total'] ?>
            </div>
        </div>
    </div>
</div>

<!-- Unmatched Alert -->
<?php if ($batch['status'] === 'draft' && $matchStats['pending'] > 0): ?>
    <div class="alert alert-warning" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; flex-shrink: 0;">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <div>
            มี <?= $matchStats['pending'] ?> รายการที่ยังไม่สามารถจับคู่ผู้คุมสอบได้
            <a href="#unmatched-section" style="color: inherit; text-decoration: underline;">ดูรายละเอียด</a>
        </div>
    </div>
<?php endif; ?>

<!-- Schedules Table -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-gray-200);">
        <h3 style="margin: 0; font-size: 1rem;">รายการตารางสอบ</h3>
        <?php if ($batch['status'] === 'draft'): ?>
            <button type="button" onclick="autoMatchAll()" class="btn btn-sm btn-secondary">
                จับคู่อัตโนมัติทั้งหมด
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto; max-height: 500px; overflow-y: auto;">
            <table class="table" style="width: 100%;">
                <thead style="position: sticky; top: 0; background: var(--color-gray-50); z-index: 10;">
                    <tr>
                        <th style="padding: 0.75rem;">วันที่</th>
                        <th style="padding: 0.75rem;">เวลา</th>
                        <th style="padding: 0.75rem;">รหัสวิชา</th>
                        <th style="padding: 0.75rem;">ชื่อวิชา</th>
                        <th style="padding: 0.75rem;">ห้อง</th>
                        <th style="padding: 0.75rem;">ประธาน (จาก Excel)</th>
                        <th style="padding: 0.75rem;">กรรมการ (จาก Excel)</th>
                        <th style="padding: 0.75rem; text-align: center;">สถานะจับคู่</th>
                        <?php if ($batch['status'] === 'draft'): ?>
                            <th style="padding: 0.75rem; text-align: center;">จัดการ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <?php
                            $hasMatch1 = false;
                            $hasMatch2 = false;
                            // Check if links exist (this is simplified, actual implementation would query the database)
                        ?>
                        <tr id="schedule-<?= $schedule['id'] ?>" style="border-bottom: 1px solid var(--color-gray-100);">
                            <td style="padding: 0.75rem;"><?= $schedule['exam_date'] ? date('d/m/Y', strtotime($schedule['exam_date'])) : '-' ?></td>
                            <td style="padding: 0.75rem;"><?= esc($schedule['exam_time_text'] ?: '-') ?></td>
                            <td style="padding: 0.75rem;"><?= esc($schedule['course_code'] ?: '-') ?></td>
                            <td style="padding: 0.75rem;"><?= esc($schedule['course_name'] ?: '-') ?></td>
                            <td style="padding: 0.75rem;"><?= esc($schedule['room'] ?: '-') ?></td>
                            <td style="padding: 0.75rem;">
                                <?php if ($schedule['examiner1_text']): ?>
                                    <span class="examiner-text" data-role="examiner1" data-value="<?= esc($schedule['examiner1_text']) ?>">
                                        <?= esc($schedule['examiner1_text']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--color-gray-400);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php if ($schedule['examiner2_text']): ?>
                                    <span class="examiner-text" data-role="examiner2" data-value="<?= esc($schedule['examiner2_text']) ?>">
                                        <?= esc($schedule['examiner2_text']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--color-gray-400);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <span class="match-status" id="status-<?= $schedule['id'] ?>">รอตรวจสอบ</span>
                            </td>
                            <?php if ($batch['status'] === 'draft'): ?>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <button type="button" onclick="openMatchModal(<?= $schedule['id'] ?>)" class="btn btn-sm btn-primary">
                                        จับคู่
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Match Modal -->
<div id="matchModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 8px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">จับคู่ผู้คุมสอบ</h3>
            <button type="button" onclick="closeMatchModal()" style="background: none; border: none; cursor: pointer;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div style="padding: 1.25rem;">
            <input type="hidden" id="matchScheduleId">
            <input type="hidden" id="matchRole">
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">ค้นหาผู้ใช้ (nickname, ชื่อ, อีเมล)</label>
                <input type="text" id="userSearchInput" class="form-control" placeholder="พิมพ์อย่างน้อย 2 ตัวอักษร..." onkeyup="searchUsers()">
            </div>
            
            <div id="userSearchResults" style="max-height: 300px; overflow-y: auto;">
                <!-- Dynamic results -->
            </div>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';
const batchId = <?= $batch['id'] ?>;

function publishBatch() {
    swalConfirm({
        title: 'ยืนยันการเผยแพร่?',
        text: 'หลังเผยแพร่ ผู้ใช้จะสามารถดูตารางสอบได้',
        confirmText: 'เผยแพร่',
        cancelText: 'ยกเลิก'
    }).then(confirmed => {
        if (!confirmed) return;
        
        fetch(`${baseUrl}admin/exam/publish/${batchId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                swalAlert(data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                swalAlert(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        })
        .catch(err => {
            swalAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            console.error(err);
        });
    });
}

function openMatchModal(scheduleId) {
    document.getElementById('matchScheduleId').value = scheduleId;
    document.getElementById('userSearchInput').value = '';
    document.getElementById('userSearchResults').innerHTML = '';
    document.getElementById('matchModal').style.display = 'flex';
}

function closeMatchModal() {
    document.getElementById('matchModal').style.display = 'none';
}

function searchUsers() {
    const query = document.getElementById('userSearchInput').value;
    if (query.length < 2) {
        document.getElementById('userSearchResults').innerHTML = '';
        return;
    }
    
    fetch(`${baseUrl}admin/exam/search-users?q=${encodeURIComponent(query)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderUserResults(data.users);
            }
        })
        .catch(console.error);
}

function renderUserResults(users) {
    const container = document.getElementById('userSearchResults');
    
    if (users.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--color-gray-600); padding: 1rem;">ไม่พบผู้ใช้</p>';
        return;
    }
    
    container.innerHTML = users.map(u => `
        <div onclick="selectUser(${u.uid}, '${u.nickname || ''}', '${u.name_th || u.name_en}')" 
             style="padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 6px; margin-bottom: 0.5rem; cursor: pointer; hover: background: var(--color-gray-50);">
            <div style="font-weight: 500;">${u.name_th || u.name_en}</div>
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">
                ${u.nickname ? `<span style="background: var(--color-primary); color: white; padding: 0.125rem 0.375rem; border-radius: 4px; margin-right: 0.5rem;">${u.nickname}</span>` : ''}
                ${u.email}
            </div>
        </div>
    `).join('');
}

function selectUser(userUid, nickname, name) {
    const scheduleId = document.getElementById('matchScheduleId').value;
    
    fetch(`${baseUrl}admin/exam/manual-match`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            schedule_id: scheduleId,
            user_uid: userUid,
            role: 'examiner1' // Simplified - should allow selecting role
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            swalAlert(data.message, 'success');
            closeMatchModal();
            document.getElementById(`status-${scheduleId}`).innerHTML = '<span style="color: var(--color-success);">✓ จับคู่แล้ว</span>';
        } else {
            swalAlert(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    })
    .catch(err => {
        swalAlert('เกิดข้อผิดพลาด', 'error');
        console.error(err);
    });
}

function autoMatchAll() {
    swalAlert('กำลังพัฒนา: จับคู่อัตโนมัติทั้งหมด', 'info');
}

// Close modal when clicking outside
document.getElementById('matchModal').addEventListener('click', function(e) {
    if (e.target === this) closeMatchModal();
});
</script>

<?= $this->endSection() ?>
