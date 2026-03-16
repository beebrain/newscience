<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <div class="exam-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 600; margin-bottom: 0.5rem;">ตารางคุมสอบ</h1>
        <p style="color: var(--color-gray-600);">ตรวจสอบตารางคุมสอบของท่านในแต่ละภาคเรียน</p>
    </div>

    <!-- Filters -->
    <div class="exam-filters" style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.375rem;">ภาคเรียน</label>
            <select id="semesterSelect" class="form-control" style="width: 100%;">
                <option value="">เลือกภาคเรียน</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.375rem;">ประเภทการสอบ</label>
            <select id="examTypeSelect" class="form-control" style="width: 100%;">
                <option value="">เลือกประเภท</option>
                <option value="midterm">สอบกลางภาค (Midterm)</option>
                <option value="final">สอบปลายภาค (Final)</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" onclick="loadSchedule()" style="min-width: 120px;">
            แสดงตาราง
        </button>
    </div>

    <!-- Loading -->
    <div id="loading" style="display: none; text-align: center; padding: 3rem;">
        <div class="spinner"></div>
        <p style="color: var(--color-gray-600); margin-top: 1rem;">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Stats Cards -->
    <div id="statsSection" style="display: none; margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-card" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 0.875rem; color: var(--color-gray-600);">รวมทั้งหมด</div>
                <div id="statTotal" style="font-size: 2rem; font-weight: 600; color: var(--color-primary);">0</div>
            </div>
            <div class="stat-card" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 0.875rem; color: var(--color-gray-600);">ประธานกรรมการ</div>
                <div id="statExaminer1" style="font-size: 2rem; font-weight: 600; color: var(--color-success);">0</div>
            </div>
            <div class="stat-card" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 0.875rem; color: var(--color-gray-600);">กรรมการ</div>
                <div id="statExaminer2" style="font-size: 2rem; font-weight: 600; color: var(--color-warning);">0</div>
            </div>
        </div>
    </div>

    <!-- Schedule Table -->
    <div id="scheduleSection" style="display: none;">
        <div class="card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-gray-50);">
                            <th style="padding: 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">วันที่</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">เวลา</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">รหัสวิชา</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">ชื่อวิชา</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">Sec</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">ห้องสอบ</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; border-bottom: 1px solid var(--color-gray-200);">หน้าที่</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleBody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" style="display: none; text-align: center; padding: 4rem 2rem;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 64px; height: 64px; color: var(--color-gray-400); margin-bottom: 1rem;">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <h3 style="font-size: 1.125rem; color: var(--color-gray-700); margin-bottom: 0.5rem;">ไม่พบตารางคุมสอบ</h3>
        <p style="color: var(--color-gray-600);">กรุณาเลือกภาคเรียนและประเภทการสอบ เพื่อตรวจสอบตารางคุมสอบของท่าน</p>
    </div>
</div>

<style>
.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--color-gray-200);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-control {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-dark);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-primary {
    background: var(--color-primary);
    color: white;
}

.badge-success {
    background: var(--color-success);
    color: white;
}

.badge-warning {
    background: var(--color-warning);
    color: #333;
}
</style>

<script>
const baseUrl = '<?= base_url() ?>';

// Load semesters on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSemesters();
});

function loadSemesters() {
    fetch(`${baseUrl}exam/get-semesters`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.semesters.length > 0) {
                const select = document.getElementById('semesterSelect');
                data.semesters.forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.semester_label;
                    option.textContent = s.semester_label;
                    select.appendChild(option);
                });
                
                // Auto-select latest
                select.value = data.semesters[0].semester_label;
            }
        })
        .catch(console.error);
}

function loadSchedule() {
    const semester = document.getElementById('semesterSelect').value;
    const examType = document.getElementById('examTypeSelect').value;
    
    if (!semester || !examType) {
        swalAlert('กรุณาเลือกภาคเรียนและประเภทการสอบ', 'warning');
        return;
    }
    
    // Show loading
    document.getElementById('loading').style.display = 'block';
    document.getElementById('scheduleSection').style.display = 'none';
    document.getElementById('statsSection').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    
    fetch(`${baseUrl}exam/get-schedule`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ semester, exam_type: examType })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        
        if (!data.success) {
            document.getElementById('emptyState').style.display = 'block';
            if (data.message) {
                swalAlert(data.message, 'info');
            }
            return;
        }
        
        if (data.schedules.length === 0) {
            document.getElementById('emptyState').style.display = 'block';
            return;
        }
        
        // Update stats
        document.getElementById('statTotal').textContent = data.stats.total;
        document.getElementById('statExaminer1').textContent = data.stats.as_examiner1;
        document.getElementById('statExaminer2').textContent = data.stats.as_examiner2;
        document.getElementById('statsSection').style.display = 'block';
        
        // Render table
        renderSchedule(data.schedules);
        document.getElementById('scheduleSection').style.display = 'block';
    })
    .catch(err => {
        document.getElementById('loading').style.display = 'none';
        swalAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        console.error(err);
    });
}

function renderSchedule(schedules) {
    const tbody = document.getElementById('scheduleBody');
    
    tbody.innerHTML = schedules.map(s => {
        const roleBadge = s.role === 'examiner1' 
            ? '<span class="badge badge-success">ประธาน</span>'
            : '<span class="badge badge-warning">กรรมการ</span>';
        
        return `
            <tr style="border-bottom: 1px solid var(--color-gray-100);">
                <td style="padding: 1rem;">${formatDate(s.exam_date)}</td>
                <td style="padding: 1rem;">${s.exam_time || '-'}</td>
                <td style="padding: 1rem;">${s.course_code || '-'}</td>
                <td style="padding: 1rem;">${s.course_name || '-'}</td>
                <td style="padding: 1rem;">${s.section || '-'}</td>
                <td style="padding: 1rem;">${s.room || '-'}</td>
                <td style="padding: 1rem; text-align: center;">${roleBadge}</td>
            </tr>
        `;
    }).join('');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    if (isNaN(date)) return dateStr;
    
    const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    const day = date.getDate();
    const month = months[date.getMonth()];
    const year = date.getFullYear() + 543;
    
    return `${day} ${month} ${year}`;
}
</script>

<?= $this->endSection() ?>
