<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('title') ?>จัดการคณะผู้ใช้<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .faculty-page {
        display: grid;
        gap: 1.25rem;
    }

    .faculty-shell {
        background: #fff;
        border: 1px solid var(--color-gray-200, #e9ecef);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .faculty-page-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--color-gray-200, #e9ecef);
        background: linear-gradient(180deg, rgba(51, 154, 240, 0.08), rgba(255, 255, 255, 0.98));
    }

    .faculty-page-header-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .faculty-title-wrap {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .faculty-title-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #339af0, #228be6);
        color: #fff;
        box-shadow: 0 10px 22px rgba(34, 139, 230, 0.24);
        flex-shrink: 0;
    }

    .faculty-title-icon svg {
        width: 22px;
        height: 22px;
    }

    .faculty-title-wrap h4 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
    }

    .faculty-page-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.92rem;
        color: #6b7280;
    }

    .faculty-stats {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .faculty-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #495057;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .faculty-stat-pill strong {
        color: #228be6;
        font-weight: 700;
    }

    .faculty-stat-pill svg {
        width: 15px;
        height: 15px;
        color: #339af0;
    }

    .faculty-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .faculty-toolbar-note {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .faculty-container {
        display: grid;
        grid-template-columns: minmax(320px, 1fr) minmax(320px, 1fr);
        gap: 20px;
        align-items: start;
    }

    .panel {
        min-width: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid #dee2e6;
    }

    .panel-header {
        padding: 1rem 1.1rem;
        background: linear-gradient(180deg, #ffffff, #f8fbff);
        border-bottom: 1px solid #dee2e6;
    }

    .panel-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
        color: #1f2937;
    }

    .panel-body {
        max-height: calc(100vh - 300px);
        min-height: 420px;
        overflow-y: auto;
        padding: 12px;
    }

    .user-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 0.8rem 0.85rem;
        margin-bottom: 0.55rem;
        background: #f8f9fa;
        border-radius: 10px;
        cursor: grab;
        transition: all 0.2s;
        border: 1px solid #e9ecef;
    }

    .user-item:hover {
        background: #eef6ff;
        border-color: #bfdbfe;
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(51, 154, 240, 0.08);
    }

    .user-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }

    .user-item.in-faculty {
        background: linear-gradient(180deg, #edf7ff, #f8fbff);
        border-color: #90caf9;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #339af0, #228be6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-weight: 600;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #1f2937;
    }

    .user-email {
        font-size: 0.8rem;
        color: #6b7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-actions {
        display: flex;
        gap: 5px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add {
        background: #e7f5ff;
        color: #1c7ed6;
        border: 1px solid #a5d8ff;
    }

    .btn-add:hover {
        background: #d0ebff;
        color: white;
        color: #1864ab;
    }

    .btn-remove {
        background: #fff1f2;
        color: #e03131;
        border: 1px solid #ffc9c9;
    }

    .btn-remove:hover {
        background: #ffe3e3;
        color: #c92a2a;
    }

    .drop-zone {
        min-height: 100px;
        border: 2px dashed #a5d8ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        margin-bottom: 12px;
        background: linear-gradient(180deg, #f8fbff, #ffffff);
        transition: all 0.3s;
    }

    .drop-zone.drag-over {
        border-color: #339af0;
        background: #e7f5ff;
        color: #1864ab;
    }

    .faculty-selector {
        margin-bottom: 15px;
    }

    .faculty-panel-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 12px;
    }

    .search-box {
        margin-bottom: 10px;
    }

    .search-box input {
        width: 100%;
        padding: 0.7rem 0.85rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        background: #fff;
        font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-box input:focus,
    .faculty-selector .form-select:focus {
        outline: none;
        border-color: #4dabf7;
        box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.12);
    }

    .pending-changes {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.14);
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 1000;
        border: 1px solid #dbeafe;
    }

    .pending-count {
        background: #228be6;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        background: linear-gradient(180deg, #fcfdff, #f8fbff);
        border: 1px dashed #dbeafe;
        border-radius: 12px;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .panel-subtitle {
        margin-top: 8px;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .panel-title-row {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .panel-title-row svg {
        width: 18px;
        height: 18px;
        color: #339af0;
        flex-shrink: 0;
    }

    .section-icon-btn {
        width: 15px;
        height: 15px;
    }

    .hidden {
        display: none !important;
    }

    @media (max-width: 991px) {
        .faculty-container {
            grid-template-columns: 1fr;
        }

        .panel-body {
            max-height: none;
            min-height: 320px;
        }

        .pending-changes {
            left: 16px;
            right: 16px;
            bottom: 16px;
            justify-content: space-between;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3 faculty-page">
    <div class="faculty-shell">
        <div class="faculty-page-header">
            <div class="faculty-page-header-top">
                <div class="faculty-title-wrap">
                    <div class="faculty-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l7-4 7 4v14"></path>
                            <path d="M9 10h.01"></path>
                            <path d="M15 10h.01"></path>
                            <path d="M9 14h.01"></path>
                            <path d="M15 14h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h4>จัดการคณะผู้ใช้</h4>
                        <p class="faculty-page-subtitle">จัดการสมาชิกแต่ละคณะด้วยการลากวางหรือกดเพิ่มและลบแบบเดียวกับหน้าจัดการใน Newscience</p>
                    </div>
                </div>
                <div class="faculty-stats">
                    <span class="faculty-stat-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        ทั้งหมด <strong id="totalCount">0</strong>
                    </span>
                    <span class="faculty-stat-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l7-4 7 4v14"></path>
                        </svg>
                        ในคณะ <strong id="facultyCount">0</strong>
                    </span>
                    <span class="faculty-stat-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        รอบันทึก <strong id="pendingCount">0</strong>
                    </span>
                </div>
            </div>
            <div class="faculty-toolbar">
                <div class="faculty-toolbar-note">อ้างอิงรูปแบบจากหน้าจัดการข่าว: ใช้ card, pill, blue accent และ icon แบบ SVG</div>
            </div>
        </div>

        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <div class="faculty-container">
                <?php $currentFaculty = $selectedFaculty ?? 'คณะวิทยาศาสตร์และเทคโนโลยี'; ?>

                <!-- Left Panel: All Users -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <h5>รายชื่อผู้ใช้ทั้งหมด</h5>
                        </div>
                        <div class="panel-subtitle">ลากรายชื่อไปยังคณะ หรือกดปุ่มเพิ่ม</div>
                        <div class="search-box mt-2">
                            <input type="text" id="searchAllUsers" placeholder="ค้นหาชื่อ, อีเมล..." oninput="filterUsers()">
                        </div>
                    </div>
                    <div class="panel-body" id="allUsersPanel">
                        <ul class="user-list" id="allUsersList">
                            <?php foreach ($users as $user): ?>
                                <li class="user-item <?= ($user['faculty'] === $currentFaculty) ? 'in-faculty' : '' ?>"
                                    data-user-id="<?= $user['uid'] ?>"
                                    data-user-name="<?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?>"
                                    data-user-email="<?= esc($user['email'] ?? '') ?>"
                                    data-user-faculty="<?= esc($user['faculty'] ?? '') ?>"
                                    data-current-faculty="<?= esc($user['faculty'] ?? '') ?>"
                                    draggable="true">
                                    <div class="user-avatar">
                                        <?= mb_substr($user['tf_name'] ?? '?', 0, 1) ?>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name"><?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?></div>
                                        <div class="user-email"><?= esc($user['email'] ?? '') ?></div>
                                    </div>
                                    <div class="user-actions">
                                        <?php if ($user['faculty'] === $currentFaculty): ?>
                                            <button class="btn-action btn-remove" onclick="removeFromFaculty(<?= $user['uid'] ?>)" title="นำออกจากคณะ">
                                                <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-add" onclick="addToFaculty(<?= $user['uid'] ?>)" title="เพิ่มเข้าคณะ">
                                                <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Panel: Faculty Members -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V7l7-4 7 4v14"></path>
                            </svg>
                            <h5>สมาชิกคณะ</h5>
                        </div>
                        <div class="panel-subtitle">เลือกคณะ แล้วลากรายชื่อจากฝั่งซ้ายมาวาง</div>
                        <div class="faculty-selector mt-2">
                            <select id="facultySelect" class="form-select" onchange="changeFaculty()">
                                <option value="">-- เลือกคณะ --</option>
                                <option value="คณะวิทยาศาสตร์และเทคโนโลยี" <?= $currentFaculty === 'คณะวิทยาศาสตร์และเทคโนโลยี' ? 'selected' : '' ?>>
                                    คณะวิทยาศาสตร์และเทคโนโลยี
                                </option>
                                <option value="คณะมนุษยศาสตร์และสังคมศาสตร์" <?= $currentFaculty === 'คณะมนุษยศาสตร์และสังคมศาสตร์' ? 'selected' : '' ?>>
                                    คณะมนุษยศาสตร์และสังคมศาสตร์
                                </option>

                                <option value="คณะเทคโนโลยีอุตสาหกรรม" <?= $currentFaculty === 'คณะเทคโนโลยีอุตสาหกรรม' ? 'selected' : '' ?>>
                                    คณะเทคโนโลยีอุตสาหกรรม
                                </option>
                                <option value="คณะเกษตรศาสตร์" <?= $currentFaculty === 'คณะเกษตรศาสตร์' ? 'selected' : '' ?>>
                                    คณะเกษตรศาสตร์
                                </option>
                                <option value="คณะวิทยาการจัดการ" <?= $currentFaculty === 'คณะวิทยาการสารสนเทศ' ? 'selected' : '' ?>>
                                    คณะวิทยาการสารสนเทศ
                                </option>
                                <option value="คณะครุศาสตร์" <?= $currentFaculty === 'คณะการบัญชีและการเงิน' ? 'selected' : '' ?>>
                                    คณะครุศาสตร์
                                </option>

                            </select>
                        </div>
                        <div class="search-box">
                            <input type="text" id="searchFacultyUsers" placeholder="ค้นหาในคณะ..." oninput="filterFacultyUsers()">
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="faculty-panel-actions">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearFacultyMembers()">
                                <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="M19 6l-1 14H6L5 6"></path>
                                </svg>
                                ล้างสมาชิกทั้งหมด
                            </button>
                        </div>
                        <div class="drop-zone" id="dropZone">
                            <div class="text-center">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 34px; height: 34px; display: block; margin: 0 auto 10px;">
                                    <path d="M20 12H8"></path>
                                    <path d="M12 16l-4-4 4-4"></path>
                                    <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                </svg>
                                ลากผู้ใช้มาวางที่นี่<br>
                                <small>หรือคลิกปุ่ม + จากรายชื่อซ้าย</small>
                            </div>
                        </div>
                        <ul class="user-list" id="facultyUsersList">
                            <?php foreach ($users as $user): ?>
                                <?php if (($user['faculty'] ?? '') === $currentFaculty): ?>
                                    <li class="user-item in-faculty"
                                        data-user-id="<?= $user['uid'] ?>"
                                        data-user-name="<?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?>"
                                        data-user-email="<?= esc($user['email'] ?? '') ?>"
                                        data-user-faculty="<?= esc($user['faculty'] ?? '') ?>"
                                        data-current-faculty="<?= esc($user['faculty'] ?? '') ?>">
                                        <div class="user-avatar">
                                            <?= mb_substr($user['tf_name'] ?? '?', 0, 1) ?>
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name"><?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?></div>
                                            <div class="user-email"><?= esc($user['email'] ?? '') ?></div>
                                        </div>
                                        <div class="user-actions">
                                            <button class="btn-action btn-remove" onclick="removeFromFaculty(<?= $user['uid'] ?>)" title="นำออกจากคณะ">
                                                <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                            </button>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="empty-state<?= array_filter($users, static fn($user) => ($user['faculty'] ?? '') === $currentFaculty) ? ' hidden' : '' ?>" id="emptyFacultyState">
                            <i class="bi bi-inbox"></i>
                            <p>ยังไม่มีสมาชิกในคณะนี้</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Changes Bar -->
<div class="pending-changes" id="pendingChangesBar" style="display: none;">
    <span>มีการเปลี่ยนแปลง <span class="pending-count" id="pendingCountBadge">0</span> รายการ</span>
    <button class="btn btn-primary btn-sm" onclick="saveChanges()">
        <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
        </svg> บันทึก
    </button>
    <button class="btn btn-outline-secondary btn-sm" onclick="clearChanges()">
        <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg> ยกเลิก
    </button>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const baseUrl = '<?= base_url() ?>';
    let pendingChanges = [];
    let selectedFaculty = '<?= esc($currentFaculty, 'js') ?>';

    document.addEventListener('DOMContentLoaded', function() {
        syncFacultyListFromSource();
        updateStats();
        setupDragAndDrop();
        filterUsers();
        filterFacultyUsers();
    });

    function changeFaculty() {
        const newFaculty = document.getElementById('facultySelect').value;
        if (newFaculty !== selectedFaculty) {
            window.location.href = `${baseUrl}admin/user-faculty?faculty=${encodeURIComponent(newFaculty)}`;
        }
    }

    function filterUsers() {
        const searchTerm = document.getElementById('searchAllUsers').value.toLowerCase();
        const items = document.querySelectorAll('#allUsersList .user-item');

        items.forEach(item => {
            const name = item.dataset.userName.toLowerCase();
            const email = item.dataset.userEmail.toLowerCase();
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function filterFacultyUsers() {
        const searchTerm = document.getElementById('searchFacultyUsers').value.toLowerCase();
        const items = document.querySelectorAll('#facultyUsersList .user-item');

        items.forEach(item => {
            const name = item.dataset.userName.toLowerCase();
            const email = item.dataset.userEmail.toLowerCase();
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function setupDragAndDrop() {
        const draggables = document.querySelectorAll('.user-item');
        const dropZone = document.getElementById('dropZone');
        const facultyList = document.getElementById('facultyUsersList');

        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('userId', this.dataset.userId);
                e.dataTransfer.setData('userName', this.dataset.userName);
                e.dataTransfer.setData('userEmail', this.dataset.userEmail);
                e.dataTransfer.setData('currentFaculty', this.dataset.currentFaculty);
                this.classList.add('dragging');
            });

            draggable.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });
        });

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            const userId = e.dataTransfer.getData('userId');
            addToFaculty(parseInt(userId));
        });

        facultyList.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        facultyList.addEventListener('drop', function(e) {
            e.preventDefault();
            const userId = e.dataTransfer.getData('userId');
            addToFaculty(parseInt(userId));
        });
    }

    function syncFacultyListFromSource() {
        const facultyList = document.getElementById('facultyUsersList');
        facultyList.innerHTML = '';

        document.querySelectorAll('#allUsersList .user-item').forEach((item) => {
            if (item.dataset.currentFaculty === selectedFaculty) {
                addToFacultyList(item);
                item.classList.add('in-faculty');
                const btnContainer = item.querySelector('.user-actions');
                if (btnContainer) {
                    btnContainer.innerHTML = `
        <button class="btn-action btn-remove" onclick="removeFromFaculty(${item.dataset.userId})" title="นำออกจากคณะ">
            <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    `;
                }
            }
        });

        toggleEmptyState();
    }

    function addToFaculty(userId) {
        if (!selectedFaculty) {
            alert('กรุณาเลือกคณะก่อน');
            return;
        }

        const userItem = document.querySelector(`#allUsersList .user-item[data-user-id="${userId}"]`);
        if (!userItem) return;

        const currentFaculty = userItem.dataset.currentFaculty;

        const existingIndex = pendingChanges.findIndex(c => c.userId === userId);
        if (existingIndex >= 0) {
            pendingChanges.splice(existingIndex, 1);
        }

        pendingChanges.push({
            userId: userId,
            userName: userItem.dataset.userName,
            oldFaculty: currentFaculty,
            newFaculty: selectedFaculty,
            action: 'add'
        });

        userItem.dataset.currentFaculty = selectedFaculty;
        userItem.classList.add('in-faculty');
        const btnContainer = userItem.querySelector('.user-actions');
        btnContainer.innerHTML = `
        <button class="btn-action btn-remove" onclick="removeFromFaculty(${userId})" title="นำออกจากคณะ">
            <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    `;

        addToFacultyList(userItem);

        updatePendingChanges();
        updateStats();
        toggleEmptyState();
    }

    function removeFromFaculty(userId) {
        const userItem = document.querySelector(`#allUsersList .user-item[data-user-id="${userId}"]`);
        if (!userItem) return;

        const existingIndex = pendingChanges.findIndex(c => c.userId === userId);
        if (existingIndex >= 0) {
            pendingChanges.splice(existingIndex, 1);
        }

        pendingChanges.push({
            userId: userId,
            userName: userItem.dataset.userName,
            oldFaculty: selectedFaculty,
            newFaculty: '',
            action: 'remove'
        });

        userItem.dataset.currentFaculty = '';
        userItem.classList.remove('in-faculty');
        const btnContainer = userItem.querySelector('.user-actions');
        btnContainer.innerHTML = `
        <button class="btn-action btn-add" onclick="addToFaculty(${userId})" title="เพิ่มเข้าคณะ">
            <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
    `;

        const facultyItem = document.querySelector(`#facultyUsersList .user-item[data-user-id="${userId}"]`);
        if (facultyItem) {
            facultyItem.remove();
        }

        updatePendingChanges();
        updateStats();
        toggleEmptyState();
    }

    function addToFacultyList(userItem) {
        const facultyList = document.getElementById('facultyUsersList');
        const userId = userItem.dataset.userId;

        if (facultyList.querySelector(`[data-user-id="${userId}"]`)) return;

        const clone = userItem.cloneNode(true);
        clone.setAttribute('draggable', 'false');
        clone.classList.remove('dragging');

        // Update button
        const btnContainer = clone.querySelector('.user-actions');
        btnContainer.innerHTML = `
        <button class="btn-action btn-remove" onclick="removeFromFaculty(${userId})" title="นำออกจากคณะ">
            <svg class="section-icon-btn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    `;

        facultyList.appendChild(clone);
    }

    function toggleEmptyState() {
        const emptyState = document.getElementById('emptyFacultyState');
        const facultyUsers = document.querySelectorAll('#facultyUsersList .user-item').length;
        emptyState.classList.toggle('hidden', facultyUsers > 0);
    }

    function updatePendingChanges() {
        const bar = document.getElementById('pendingChangesBar');
        const badge = document.getElementById('pendingCountBadge');

        if (pendingChanges.length > 0) {
            bar.style.display = 'flex';
            badge.textContent = pendingChanges.length;
        } else {
            bar.style.display = 'none';
        }
    }

    function updateStats() {
        const totalUsers = document.querySelectorAll('#allUsersList .user-item').length;
        const facultyUsers = document.querySelectorAll('#facultyUsersList .user-item').length;

        document.getElementById('totalCount').textContent = totalUsers;
        document.getElementById('facultyCount').textContent = facultyUsers;
        document.getElementById('pendingCount').textContent = pendingChanges.length;
    }

    async function clearFacultyMembers() {
        const facultyItems = Array.from(document.querySelectorAll('#facultyUsersList .user-item'));

        if (!selectedFaculty) {
            window.swalAlert('กรุณาเลือกคณะก่อน', 'warning');
            return;
        }

        if (facultyItems.length === 0) {
            window.swalAlert('คณะนี้ยังไม่มีสมาชิกให้ล้าง', 'info');
            return;
        }

        const confirmed = await window.swalConfirm({
            title: 'ล้างสมาชิกทั้งหมดของคณะ?',
            text: `สมาชิก ${facultyItems.length} คนจะถูกนำออกจาก ${selectedFaculty}`,
            confirmText: 'ล้างทั้งหมด',
            cancelText: 'ยกเลิก'
        });

        if (!confirmed) {
            return;
        }

        facultyItems.forEach((item) => {
            removeFromFaculty(parseInt(item.dataset.userId, 10));
        });
    }

    async function saveChanges() {
        if (pendingChanges.length === 0) return;

        const changesToSave = [...pendingChanges];

        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล',
                    text: 'โปรดรอสักครู่ ระบบกำลังประมวลผลการเปลี่ยนแปลง',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            for (const change of changesToSave) {
                const response = await fetch(`${baseUrl}admin/user-faculty/update-faculty`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${change.userId}&faculty=${encodeURIComponent(change.newFaculty)}`
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'ไม่สามารถบันทึกข้อมูลได้');
                }
            }

            pendingChanges = [];
            updatePendingChanges();
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ',
                    text: `บันทึกข้อมูล ${changesToSave.length} รายการเรียบร้อยแล้ว`,
                    confirmButtonText: 'ตกลง'
                });
            } else {
                alert(`บันทึกสำเร็จ: ${changesToSave.length} รายการ`);
            }

            window.location.reload();
        } catch (error) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: error.message
                });
            } else {
                alert('เกิดข้อผิดพลาด: ' + error.message);
            }
        }
    }

    // Clear all pending changes
    function clearChanges() {
        if (!confirm('ยกเลิกการเปลี่ยนแปลงทั้งหมด?')) return;

        window.location.reload();
    }
</script>
<?= $this->endSection() ?>