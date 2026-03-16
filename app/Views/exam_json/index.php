<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<?php
// Use user data from controller (from user table) instead of session directly
$currentUser = $current_user ?? [];
$nickname = trim((string)($currentUser['nickname'] ?? ''));
$thaiName = trim((string)($currentUser['thai_name'] ?? ''));
$matchName = $nickname ?: $thaiName;
?>

<div class="container" style="max-width: 1400px; margin: 0 auto; padding: 2rem 1rem;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">ตารางคุมสอบ</h1>
        <p class="text-muted" style="margin: 0;">ตรวจสอบตารางคุมสอบของคุณ</p>
    </div>

    <div class="exam-card" style="margin-bottom: 1.5rem;">
        <div class="filter-grid">
            <div>
                <label class="form-label" for="semesterSelect">ภาคการศึกษา</label>
                <select id="semesterSelect" class="form-control">
                    <option value="">-- เลือกภาคการศึกษา --</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="examTypeSelect">ประเภทการสอบ</label>
                <select id="examTypeSelect" class="form-control">
                    <option value="">-- เลือก --</option>
                    <option value="midterm">กลางภาค</option>
                    <option value="final">ปลายภาค</option>
                </select>
            </div>
            <div class="match-box">
                <div class="match-box__label">ชื่อที่ใช้จับคู่</div>
                <div class="match-box__value"><?= esc($matchName ?: 'ไม่พบข้อมูลชื่อสำหรับจับคู่') ?></div>
                <div class="match-box__hint">ระบบจับคู่จาก Nickname และชื่อไทย</div>
            </div>
        </div>
    </div>

    <div id="summarySection" style="display: none; margin-bottom: 1.5rem;">
        <div class="summary-grid">
            <div class="summary-card">
                <div id="totalCount" class="summary-value">0</div>
                <div class="summary-label">รายการทั้งหมด</div>
            </div>
            <div class="summary-card">
                <div id="myCount" class="summary-value success">0</div>
                <div class="summary-label">ตารางของฉัน</div>
            </div>
            <div class="summary-card">
                <div id="instructorCount" class="summary-value primary">0</div>
                <div class="summary-label">รายชื่ออาจารย์</div>
            </div>
        </div>
    </div>

    <div id="stateInitial" class="state-card">
        <p id="initialMessage" style="margin: 0; color: var(--color-gray-600);">เลือกภาคการศึกษาและประเภทการสอบเพื่อดูตาราง</p>
    </div>

    <div id="stateLoading" class="state-card" style="display: none; text-align: center;">
        <div class="spinner"></div>
        <p style="margin: 0; color: var(--color-gray-600);">กำลังโหลดข้อมูล...</p>
    </div>

    <div id="stateError" class="state-card" style="display: none;">
        <p id="errorMessage" style="margin: 0; color: var(--color-danger); font-weight: 600;">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
    </div>

    <div id="contentSection" style="display: none;">
        <div class="tabs-row" id="tabButtons" style="margin-bottom: 1rem;">
            <button type="button" class="tab-btn active" data-tab="all">ตารางรวมทั้งหมด</button>
            <button type="button" class="tab-btn" data-tab="mine">ตารางของฉัน</button>
            <button type="button" class="tab-btn" data-tab="instructors">ตารางอาจารย์อื่นๆ</button>
        </div>

        <div id="panelAll" class="tab-panel">
            <div class="table-card">
                <div class="table-wrap">
                    <table class="exam-table">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>เวลา</th>
                                <th>รหัสวิชา</th>
                                <th>ชื่อวิชา</th>
                                <th>กลุ่ม</th>
                                <th>ห้อง</th>
                                <th>อาจารย์เจ้าของรายวิชา</th>
                                <th>ผู้คุมสอบ 1</th>
                                <th>ผู้คุมสอบ 2</th>
                            </tr>
                        </thead>
                        <tbody id="allSchedulesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="panelMine" class="tab-panel" style="display: none;">
            <div class="table-card">
                <div class="table-wrap">
                    <table class="exam-table">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>เวลา</th>
                                <th>รหัสวิชา</th>
                                <th>ชื่อวิชา</th>
                                <th>กลุ่ม</th>
                                <th>ห้อง</th>
                                <th>อาจารย์เจ้าของรายวิชา</th>
                                <th>ผู้คุมสอบ 1</th>
                                <th>ผู้คุมสอบ 2</th>
                                <th>บทบาท</th>
                            </tr>
                        </thead>
                        <tbody id="mySchedulesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="panelInstructors" class="tab-panel" style="display: none;">
            <div class="exam-card">
                <div id="instructorCards" class="cards-grid"></div>
            </div>
        </div>
    </div>
</div>

<div id="instructorModal" class="modal-backdrop" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="modalTitle" style="margin: 0; font-size: 1.2rem;">รายละเอียดอาจารย์</h3>
            <button type="button" id="closeModalBtn" class="modal-close">&times;</button>
        </div>
        <div id="modalContent"></div>
    </div>
</div>

<style>
    .exam-card,
    .table-card,
    .state-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .table-card {
        padding: 0;
        overflow: hidden;
    }

    .filter-grid,
    .summary-grid {
        display: grid;
        gap: 1rem;
    }

    .filter-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        align-items: end;
    }

    .summary-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }

    .match-box {
        min-height: 76px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0.875rem 1rem;
        border-radius: 10px;
        background: var(--color-gray-50);
        border: 1px solid var(--color-gray-200);
    }

    .match-box__label,
    .summary-label,
    .badge-role,
    .chip-meta,
    .owner-course-meta {
        font-size: 0.75rem;
    }

    .match-box__label,
    .match-box__hint,
    .summary-label,
    .chip-meta,
    .owner-course-meta {
        color: var(--color-gray-600);
    }

    .match-box__value,
    .summary-value {
        font-weight: 600;
        color: var(--color-gray-800);
    }

    .summary-card {
        padding: 1rem;
        border-radius: 10px;
        border: 1px solid var(--color-gray-200);
        background: white;
        text-align: center;
    }

    .summary-value {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }

    .summary-value.primary {
        color: var(--color-primary);
    }

    .summary-value.success {
        color: var(--color-success);
    }

    .tabs-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .tab-btn {
        border: 1px solid var(--color-gray-300);
        background: white;
        color: var(--color-gray-700);
        border-radius: 999px;
        padding: 0.65rem 1rem;
        cursor: pointer;
        font-weight: 500;
    }

    .tab-btn.active {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .exam-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: collapse;
    }

    .exam-table th,
    .exam-table td {
        padding: 0.85rem;
        border-bottom: 1px solid var(--color-gray-200);
        text-align: left;
        vertical-align: top;
        font-size: 0.875rem;
    }

    .exam-table th {
        background: var(--color-gray-50);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .exam-table tbody tr:hover {
        background: var(--color-gray-50);
    }

    .chips-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .instructor-card {
        background: white;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .instructor-card:hover {
        border-color: var(--color-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .instructor-card__name {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--color-gray-800);
        margin-bottom: 0.75rem;
    }

    .instructor-card__stats {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 0.5rem;
    }

    .instructor-card__stat {
        background: var(--color-gray-50);
        border-radius: 999px;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        color: var(--color-gray-600);
    }

    .instructor-card__stat.primary {
        background: rgba(var(--color-primary-rgb), 0.1);
        color: var(--color-primary);
    }

    .instructor-card__stat.success {
        background: rgba(var(--color-success-rgb), 0.1);
        color: var(--color-success);
    }

    .instructor-card__match {
        margin-top: 0.75rem;
        padding: 0.5rem;
        background: rgba(var(--color-warning-rgb), 0.1);
        border-radius: 8px;
        font-size: 0.75rem;
        color: var(--color-warning-700);
        border: 1px solid rgba(var(--color-warning-rgb), 0.2);
    }

    .chip-btn {
        border: 1px solid var(--color-gray-300);
        background: white;
        border-radius: 999px;
        padding: 0.65rem 0.9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chip-btn:hover {
        border-color: var(--color-primary);
        color: var(--color-primary);
    }

    .chip-meta {
        background: var(--color-gray-50);
        border-radius: 999px;
        padding: 0.15rem 0.45rem;
    }

    .badges-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .badge-role {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        background: rgba(var(--color-success-rgb), 0.12);
        color: var(--color-success);
        font-weight: 600;
    }

    .state-card {
        margin-bottom: 1rem;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--color-gray-200);
        border-top-color: var(--color-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1000;
        padding: 1rem;
        align-items: center;
        justify-content: center;
    }

    .modal-card {
        max-width: 960px;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
        background: white;
        border-radius: 14px;
        padding: 1.5rem;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .modal-close {
        border: none;
        background: none;
        font-size: 1.75rem;
        color: var(--color-gray-500);
        cursor: pointer;
        line-height: 1;
    }

    .modal-section {
        margin-bottom: 1.5rem;
    }

    .modal-section h4 {
        margin: 0 0 0.75rem 0;
        font-size: 1rem;
    }

    .modal-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal-table th,
    .modal-table td {
        padding: 0.65rem;
        border-bottom: 1px solid var(--color-gray-200);
        text-align: left;
        font-size: 0.875rem;
        vertical-align: top;
    }

    .modal-table th {
        background: var(--color-gray-50);
        font-size: 0.8rem;
    }

    .owner-courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.75rem;
    }

    .owner-course-card {
        padding: 0.85rem;
        border-radius: 10px;
        background: var(--color-gray-50);
        border: 1px solid var(--color-gray-200);
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    const baseUrl = '<?= base_url() ?>';
    const isAdmin = <?= (session()->get('admin_role') ? 'true' : 'false') ?>;
    const state = {
        activeTab: 'all',
        loading: false,
        summary: {
            total_schedules: 0,
            my_schedules: 0,
            instructor_count: 0,
        },
        allSchedules: [],
        mySchedules: [],
        instructors: [],
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('semesterSelect').addEventListener('change', handleFilterChange);
        document.getElementById('examTypeSelect').addEventListener('change', handleFilterChange);
        document.getElementById('tabButtons').addEventListener('click', handleTabClick);
        document.getElementById('closeModalBtn').addEventListener('click', closeInstructorModal);
        document.getElementById('instructorModal').addEventListener('click', (event) => {
            if (event.target.id === 'instructorModal') {
                closeInstructorModal();
            }
        });
        loadSemesters();
    });

    function handleTabClick(event) {
        const button = event.target.closest('.tab-btn');
        if (!button) {
            return;
        }
        setActiveTab(button.dataset.tab);
    }

    function handleFilterChange() {
        const semester = document.getElementById('semesterSelect').value;
        const examType = document.getElementById('examTypeSelect').value;

        if (!semester || !examType) {
            resetContent();
            return;
        }

        loadSchedules(semester, examType);
    }

    function resetContent() {
        state.allSchedules = [];
        state.mySchedules = [];
        state.instructors = [];
        state.summary = {
            total_schedules: 0,
            my_schedules: 0,
            instructor_count: 0,
        };
        document.getElementById('summarySection').style.display = 'none';
        document.getElementById('contentSection').style.display = 'none';
        document.getElementById('stateLoading').style.display = 'none';
        document.getElementById('stateError').style.display = 'none';
        document.getElementById('stateInitial').style.display = 'block';
        document.getElementById('initialMessage').textContent = 'เลือกภาคการศึกษาและประเภทการสอบเพื่อดูตาราง';
    }

    function setLoading(isLoading) {
        state.loading = isLoading;
        document.getElementById('stateLoading').style.display = isLoading ? 'block' : 'none';
        document.getElementById('stateInitial').style.display = isLoading ? 'none' : document.getElementById('stateInitial').style.display;
        if (isLoading) {
            document.getElementById('stateError').style.display = 'none';
            document.getElementById('contentSection').style.display = 'none';
        }
    }

    function setError(message) {
        document.getElementById('summarySection').style.display = 'none';
        document.getElementById('contentSection').style.display = 'none';
        document.getElementById('stateInitial').style.display = 'none';
        document.getElementById('stateLoading').style.display = 'none';
        document.getElementById('stateError').style.display = 'block';
        document.getElementById('errorMessage').textContent = message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    }

    function showContent() {
        document.getElementById('stateInitial').style.display = 'none';
        document.getElementById('stateError').style.display = 'none';
        document.getElementById('stateLoading').style.display = 'none';
        document.getElementById('summarySection').style.display = 'block';
        document.getElementById('contentSection').style.display = 'block';
    }

    function loadSemesters() {
        fetch(`${baseUrl}exam/get-semesters`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success || !Array.isArray(data.semesters)) {
                    setError('ไม่สามารถโหลดรายการภาคการศึกษาได้');
                    return;
                }

                const select = document.getElementById('semesterSelect');
                select.innerHTML = '<option value="">-- เลือกภาคการศึกษา --</option>';

                data.semesters.forEach((semester) => {
                    const option = document.createElement('option');
                    option.value = semester.label;
                    option.textContent = `ภาค ${semester.label}`;
                    select.appendChild(option);
                });
            })
            .catch(() => {
                setError('ไม่สามารถโหลดรายการภาคการศึกษาได้');
            });
    }

    function loadSchedules(semester, examType) {
        setLoading(true);

        fetch(`${baseUrl}exam/get-schedules?semester=${encodeURIComponent(semester)}&exam_type=${encodeURIComponent(examType)}`)
            .then((response) => response.json())
            .then((data) => {
                setLoading(false);

                if (!data.success) {
                    setError(data.message || 'ไม่สามารถโหลดข้อมูลตารางสอบได้');
                    return;
                }

                state.summary = data.summary || {
                    total_schedules: 0,
                    my_schedules: 0,
                    instructor_count: 0,
                };
                state.allSchedules = Array.isArray(data.schedules_all) ? data.schedules_all : [];
                state.mySchedules = Array.isArray(data.schedules_mine) ? data.schedules_mine : [];
                state.instructors = Array.isArray(data.instructors) ? data.instructors : [];

                renderSummary();
                showContent();
                setActiveTab(state.activeTab);
            })
            .catch(() => {
                setLoading(false);
                setError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            });
    }

    function renderSummary() {
        document.getElementById('totalCount').textContent = state.summary.total_schedules || 0;
        document.getElementById('myCount').textContent = state.summary.my_schedules || 0;
        document.getElementById('instructorCount').textContent = state.summary.instructor_count || 0;
    }

    function setActiveTab(tabName) {
        state.activeTab = tabName;

        document.querySelectorAll('.tab-btn').forEach((button) => {
            button.classList.toggle('active', button.dataset.tab === tabName);
        });

        document.getElementById('panelAll').style.display = tabName === 'all' ? 'block' : 'none';
        document.getElementById('panelMine').style.display = tabName === 'mine' ? 'block' : 'none';
        document.getElementById('panelInstructors').style.display = tabName === 'instructors' ? 'block' : 'none';

        if (tabName === 'all') {
            renderAllSchedules();
        } else if (tabName === 'mine') {
            renderMySchedules();
        } else {
            renderInstructorChips();
        }
    }

    function renderAllSchedules() {
        const tbody = document.getElementById('allSchedulesTableBody');
        if (!state.allSchedules.length) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem;">ไม่พบข้อมูลตารางสอบ</td></tr>';
            return;
        }

        tbody.innerHTML = state.allSchedules.map((row) => `
            <tr>
                <td>${escapeHtml(row.exam_date)}</td>
                <td>${escapeHtml(row.exam_time)}</td>
                <td>${escapeHtml(row.course_code)}</td>
                <td>${escapeHtml(row.course_name)}</td>
                <td>${escapeHtml(row.student_group)}</td>
                <td>${escapeHtml(row.room)}</td>
                <td>${escapeHtml(row.instructor)}</td>
                <td>${escapeHtml(row.examiner1)}</td>
                <td>${escapeHtml(row.examiner2)}</td>
            </tr>
        `).join('');
    }

    function renderMySchedules() {
        const tbody = document.getElementById('mySchedulesTableBody');
        if (!state.mySchedules.length) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding:2rem;">ไม่พบตารางที่เกี่ยวข้องกับคุณ</td></tr>';
            return;
        }

        tbody.innerHTML = state.mySchedules.map((row) => `
            <tr>
                <td>${escapeHtml(row.exam_date)}</td>
                <td>${escapeHtml(row.exam_time)}</td>
                <td>${escapeHtml(row.course_code)}</td>
                <td>${escapeHtml(row.course_name)}</td>
                <td>${escapeHtml(row.student_group)}</td>
                <td>${escapeHtml(row.room)}</td>
                <td>${escapeHtml(row.instructor)}</td>
                <td>${escapeHtml(row.examiner1)}</td>
                <td>${escapeHtml(row.examiner2)}</td>
                <td><div class="badges-wrap">${(row.roles || []).map((role) => `<span class="badge-role">${escapeHtml(role)}</span>`).join('')}</div></td>
            </tr>
        `).join('');
    }

    function renderInstructorChips() {
        const container = document.getElementById('instructorCards');
        if (!state.instructors.length) {
            container.innerHTML = '<p style="margin:0; color: var(--color-gray-600);">ไม่พบข้อมูลอาจารย์</p>';
            return;
        }

        // Get current user data for matching
        const currentUserNickname = '<?= esc($currentUser['nickname'] ?? '') ?>';
        const currentUserThaiName = '<?= esc($currentUser['thai_name'] ?? '') ?>';

        // Helper function to normalize names for comparison
        function normalizeNameForMatch(name) {
            if (!name) return '';
            return name.toLowerCase()
                .trim()
                .replace(/\s+/g, ' ') // Normalize multiple spaces to single
                .replace(/[,\s]+/g, ' '); // Remove commas and normalize spaces
        }

        // Helper function to check if two names represent the same person
        function isSamePerson(name1, name2) {
            const norm1 = normalizeNameForMatch(name1);
            const norm2 = normalizeNameForMatch(name2);

            // Exact match
            if (norm1 === norm2) return true;

            // Check partial matching (handles cases like "พิศิษฐ์" vs "พิศิษฐ์ นาคใจ")
            const parts1 = norm1.split(' ');
            const parts2 = norm2.split(' ');

            // If one is a single word, only treat it as the same person when it matches
            // the first token of the longer name, not just any substring inside a word.
            if (parts1.length === 1 && parts2.length > 1) return parts2[0] === norm1;
            if (parts2.length === 1 && parts1.length > 1) return parts1[0] === norm2;

            // For multi-part names, require the first token to match and one side to be
            // a token-boundary prefix of the other.
            if (parts1.length > 1 && parts2.length > 1 && parts1[0] === parts2[0]) {
                const shorter = parts1.length <= parts2.length ? parts1 : parts2;
                const longer = parts1.length > parts2.length ? parts1 : parts2;
                return shorter.every((part, idx) => longer[idx] === part);
            }

            return false;
        }

        // Helper function to check if instructor matches current user
        function isCurrentUser(instructorName) {
            if (!instructorName) return false;
            if (!currentUserNickname && !currentUserThaiName) return false;

            const normalizedInstructor = normalizeNameForMatch(instructorName);

            // Check nickname match
            if (currentUserNickname) {
                const normalizedNickname = normalizeNameForMatch(currentUserNickname);
                if (normalizedInstructor === normalizedNickname) {
                    return true;
                }
            }

            // Check full Thai name match
            if (currentUserThaiName) {
                const normalizedThaiName = normalizeNameForMatch(currentUserThaiName);

                // Exact match
                if (normalizedInstructor === normalizedThaiName) {
                    return true;
                }

                // Check if instructor name contains user's Thai name or vice versa
                if (normalizedInstructor.includes(normalizedThaiName) ||
                    normalizedThaiName.includes(normalizedInstructor)) {
                    return true;
                }

                // Check partial matching for names with spaces
                const instructorParts = normalizedInstructor.split(' ');
                const thaiNameParts = normalizedThaiName.split(' ');

                // Check if any part matches
                if (instructorParts.some(part =>
                        part.length > 1 && thaiNameParts.includes(part))) {
                    return true;
                }

                // Special handling: Check if instructor name is a single word that matches
                // any part of the user's full name
                if (instructorParts.length === 1 && thaiNameParts.length > 1) {
                    if (thaiNameParts.includes(normalizedInstructor)) {
                        return true;
                    }
                }
            }

            return false;
        }

        // Group instructors that are the same person
        const groupedInstructors = [];
        const processedIndices = new Set();

        state.instructors.forEach((instructor, index) => {
            if (processedIndices.has(index)) return;

            const group = {
                names: [instructor.name],
                exam_schedules: [...(instructor.exam_schedules || [])],
                owner_courses: [...(instructor.owner_courses || [])],
                originalIndices: [index]
            };

            // Find all instructors that are the same person
            state.instructors.forEach((otherInstructor, otherIndex) => {
                if (index === otherIndex || processedIndices.has(otherIndex)) return;

                if (isSamePerson(instructor.name, otherInstructor.name)) {
                    group.names.push(otherInstructor.name);
                    group.exam_schedules.push(...(otherInstructor.exam_schedules || []));
                    group.owner_courses.push(...(otherInstructor.owner_courses || []));
                    group.originalIndices.push(otherIndex);
                    processedIndices.add(otherIndex);
                }
            });

            processedIndices.add(index);
            groupedInstructors.push(group);
        });

        container.innerHTML = groupedInstructors.map((group, groupIndex) => {
            // Use the longest name as the display name (usually the full name)
            const displayName = group.names.reduce((longest, current) =>
                current.length > longest.length ? current : longest
            );

            const isCurrentUserInstructor = isCurrentUser(displayName);
            const examCount = group.exam_schedules.length;
            const ownerCount = group.owner_courses.length;

            // Create unique identifier for this group
            const groupIdentifier = `group-${groupIndex}`;

            return `
                <div class="instructor-card ${isCurrentUserInstructor ? 'current-user' : ''}" 
                     data-group-identifier="${groupIdentifier}"
                     style="${isCurrentUserInstructor ? 'border: 2px solid var(--color-primary); background: rgba(var(--color-primary-rgb), 0.05);' : ''}">
                    <div class="instructor-card__name">
                        ${escapeHtml(displayName)}
                        ${isCurrentUserInstructor ? '<span style="font-size: 0.75rem; color: var(--color-primary); margin-left: 0.5rem;">(คุณ)</span>' : ''}
                    </div>
                    ${group.names.length > 1 ? `
                        <div style="font-size: 0.75rem; color: var(--color-gray-500); margin-bottom: 0.5rem;">
                            รวมจาก: ${group.names.map(name => escapeHtml(name)).join(', ')}
                        </div>
                    ` : ''}
                    ${isCurrentUserInstructor ? '<div class="instructor-card__match">✓ ตรงกับข้อมูลของคุณ</div>' : ''}
                </div>
            `;
        }).join('');

        container.querySelectorAll('.instructor-card').forEach((card) => {
            card.addEventListener('click', () => {
                const groupIdentifier = card.dataset.groupIdentifier;
                const groupIndex = parseInt(groupIdentifier.replace('group-', ''));

                if (groupIndex >= 0 && groupedInstructors[groupIndex]) {
                    // Create a merged instructor object for the modal
                    const mergedInstructor = {
                        name: groupedInstructors[groupIndex].names.reduce((longest, current) =>
                            current.length > longest.length ? current : longest
                        ),
                        exam_schedules: groupedInstructors[groupIndex].exam_schedules,
                        owner_courses: groupedInstructors[groupIndex].owner_courses
                    };
                    openInstructorModal(mergedInstructor);
                }
            });
        });
    }

    function openInstructorModal(instructor) {
        const modal = document.getElementById('instructorModal');
        const title = document.getElementById('modalTitle');
        const content = document.getElementById('modalContent');
        if (!modal || !title || !content) {
            return;
        }

        title.textContent = `รายละเอียดอาจารย์: ${instructor.name || '-'}`;

        const examSchedules = Array.isArray(instructor.exam_schedules) ? instructor.exam_schedules : [];
        const ownerCourses = Array.isArray(instructor.owner_courses) ? instructor.owner_courses : [];

        const examSchedulesHtml = examSchedules.length ?
            `
                <div class="modal-section">
                    <h4>ตารางคุมสอบ</h4>
                    <div class="table-wrap">
                        <table class="modal-table">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>เวลา</th>
                                    <th>รหัสวิชา</th>
                                    <th>ชื่อวิชา</th>
                                    <th>ห้อง</th>
                                    <th>บทบาท</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${examSchedules.map((row) => `
                                    <tr>
                                        <td>${escapeHtml(row.exam_date)}</td>
                                        <td>${escapeHtml(row.exam_time)}</td>
                                        <td>${escapeHtml(row.course_code)}</td>
                                        <td>${escapeHtml(row.course_name)}</td>
                                        <td>${escapeHtml(row.room)}</td>
                                        <td>${escapeHtml(row.role)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            ` :
            `
                <div class="modal-section">
                    <h4>ตารางคุมสอบ</h4>
                    <p style="margin:0; color: var(--color-gray-600);">ไม่มีข้อมูลตารางคุมสอบ</p>
                </div>
            `;

        const ownerCoursesHtml = ownerCourses.length ?
            `
                <div class="modal-section">
                    <h4>รายวิชาที่เป็นเจ้าของ</h4>
                    <div class="owner-courses-grid">
                        ${ownerCourses.map((course) => `
                            <div class="owner-course-card">
                                <div style="font-weight:600; margin-bottom:0.25rem;">${escapeHtml(course.course_code)}</div>
                                <div style="margin-bottom:0.25rem;">${escapeHtml(course.course_name)}</div>
                                <div class="owner-course-meta">Section ${escapeHtml(course.section)} | กลุ่ม ${escapeHtml(course.student_group)}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` :
            `
                <div class="modal-section">
                    <h4>รายวิชาที่เป็นเจ้าของ</h4>
                    <p style="margin:0; color: var(--color-gray-600);">ไม่มีข้อมูลรายวิชาเจ้าของ</p>
                </div>
            `;

        content.innerHTML = examSchedulesHtml + ownerCoursesHtml;
        modal.style.display = 'flex';
    }

    function closeInstructorModal() {
        const modal = document.getElementById('instructorModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function escapeHtml(value) {
        const text = value == null || value === '' ? '-' : String(value);
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?= $this->endSection() ?>