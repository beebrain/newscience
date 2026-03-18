<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php helper('url');
$base = base_url();
$adminBase = rtrim($base, '/') . '/evaluate/admin';
?>

<style>
    .evaluate-shell {
        display: grid;
        gap: 1.5rem;
        font-family: var(--font-primary);
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .evaluate-shell,
    .evaluate-shell input,
    .evaluate-shell textarea,
    .evaluate-shell select,
    .evaluate-shell button,
    .evaluate-shell .btn,
    .evaluate-shell .badge,
    .evaluate-shell .table,
    .evaluate-shell .modal-box {
        font-family: var(--font-primary);
    }

    .evaluate-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 16px rgba(3, 105, 161, 0.3);
    }

    .evaluate-header-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .evaluate-header-content h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .evaluate-header-content p {
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }

    .evaluate-header__actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .evaluate-header h2,
    .card-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-gray-900);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #0369a1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
    }

    .stat-icon.blue {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .stat-icon.amber {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-icon.emerald {
        background: #d1fae5;
        color: #059669;
    }

    .stat-icon.purple {
        background: #ede9fe;
        color: #7c3aed;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #0c4a6e;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .evaluate-search-bar {
        display: flex;
        gap: 1rem;
        align-items: stretch;
        flex-wrap: wrap;
        background: #f8f9fa;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        max-width: 500px;
    }

    .evaluate-search-bar .form-control {
        flex: 1;
        min-width: 300px;
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .evaluate-search-bar .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .news-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: var(--color-blue-50);
        color: var(--color-blue-700);
        border: 1px solid var(--color-blue-200);
        font-size: 0.875rem;
        font-weight: 600;
    }

    .form-section {
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 1rem;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin: 0 0 1rem 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-gray-900);
    }

    .form-section-title i {
        color: var(--color-blue-600);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .badge-success {
        background: #e8f5e9;
        color: #2e7d32;
        border-color: #a5d6a7;
    }

    .badge-warning {
        background: #fff8e1;
        color: #f57f17;
        border-color: #ffd54f;
    }

    .badge-secondary {
        background: #f1f3f5;
        color: #495057;
        border-color: #dee2e6;
    }

    /* Modern Table Styling */
    .news-table-wrap {
        overflow-x: auto;
        border-radius: 0 0 12px 12px;
    }

    .table {
        margin: 0;
        font-size: 0.875rem;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        color: #475569;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f8fafc;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        color: #1e3a6e;
        margin-right: 0.75rem;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-name {
        font-weight: 600;
        color: #1e293b;
    }

    /* Flat Underline Tabs */
    .flat-tabs {
        display: flex;
        border-bottom: 1px solid #e2e8f0;
        padding: 0 1.5rem;
        background: white;
        flex-shrink: 0;
    }

    .flat-tab-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        transition: all 0.15s ease;
        margin-bottom: -1px;
        font-family: var(--font-primary);
    }

    .flat-tab-btn:hover {
        color: #334155;
        border-bottom-color: #cbd5e1;
    }

    .flat-tab-btn.active {
        color: #0369a1;
        font-weight: 600;
        border-bottom-color: #0369a1;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    @media (max-width: 576px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.875rem 1rem;
    }

    .info-card-label {
        font-size: 0.7rem;
        color: #94a3b8;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .info-card-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9375rem;
    }

    .info-card.blue {
        background: #e0f2fe;
        border-color: #bae6fd;
    }

    .info-card.blue .info-card-label {
        color: #0ea5e9;
    }

    .info-card.blue .info-card-value {
        color: #0369a1;
    }

    .info-card.purple {
        background: #f5f3ff;
        border-color: #ddd6fe;
    }

    .info-card.purple .info-card-label {
        color: #8b5cf6;
    }

    .info-card.purple .info-card-value {
        color: #6d28d9;
    }

    /* Evaluator Cards */
    .eval-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 0.75rem;
    }

    .eval-card-header {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 0.875rem;
    }

    .eval-num-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .eval-num-badge.n1 {
        background: #e0f2fe;
        color: #0369a1;
    }

    .eval-num-badge.n2 {
        background: #fef3c7;
        color: #d97706;
    }

    .eval-num-badge.n3 {
        background: #ede9fe;
        color: #7c3aed;
    }

    .eval-card-body {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .eval-card-body select {
        flex: 1;
        min-width: 200px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        background: white;
        font-family: var(--font-primary);
    }

    .eval-card-body select:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.3);
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1px solid var(--color-gray-300);
        min-height: 42px;
        font-size: 0.875rem;
        box-shadow: none;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #4dabf7;
        box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.12);
    }

    .actions {
        display: inline-flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .evaluate-shell .btn {
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .evaluate-shell .btn-primary {
        background: #339af0;
        border-color: #339af0;
    }

    .evaluate-shell .btn-primary:hover {
        background: #228be6;
        border-color: #228be6;
    }

    .evaluate-shell .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
    }

    .evaluate-shell .btn-info {
        background: #e7f5ff;
        border-color: #a5d8ff;
        color: #1971c2;
    }

    .evaluate-shell .btn-warning {
        background: #fff4e6;
        border-color: #ffd8a8;
        color: #e67700;
    }

    .evaluate-shell .btn-success {
        background: #ebfbee;
        border-color: #b2f2bb;
        color: #2b8a3e;
    }

    .evaluate-shell .btn-danger {
        background: #fff5f5;
        border-color: #ffc9c9;
        color: #e03131;
    }

    .evaluate-shell .btn-danger:hover {
        background: #ff8787;
        border-color: #ff6b6b;
        color: #fff;
    }

    #evaluationTable thead th {
        background: #f8f9fa;
        color: #495057;
        padding: 0.8rem 1rem;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--color-gray-200);
        font-weight: 600;
    }

    #evaluationTable tbody td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    #evaluationTable tbody tr:hover {
        background: #fcfcfd;
    }

    .empty-state p,
    .empty-state h3,
    .empty-state {
        color: var(--color-gray-600);
    }

    /* Modal */
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        color: white;
        flex-shrink: 0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-close {
        border: none;
        background: rgba(255, 255, 255, 0.15);
        color: white !important;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    .modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
        background: #f8fafc;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #fff;
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 1100;
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-box {
        position: relative;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 25px 60px rgba(3, 105, 161, 0.2);
        width: min(100%, 1000px);
        max-height: calc(100vh - 2rem);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: modalIn 0.25s ease-out;
    }

    @keyframes modalIn {
        from {
            opacity: 0;
            transform: translateY(16px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    #searchResults .table-responsive,
    #searchResults .alert {
        font-size: 0.875rem;
    }

    /* Inline search in card header */
    .card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: white;
    }

    .card-header-row h2 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: #0369a1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .inline-search {
        position: relative;
    }

    .inline-search i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.875rem;
        pointer-events: none;
    }

    .inline-search input {
        padding: 0.5rem 1rem 0.5rem 2.25rem;
        font-size: 0.875rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        width: 220px;
        font-family: var(--font-primary);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .inline-search input:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.3);
    }

    @media (max-width: 768px) {
        .evaluate-shell {
            padding: 0 0.5rem;
            gap: 1rem;
        }

        .evaluate-header {
            padding: 1.5rem 1rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .evaluate-header h2 {
            font-size: 1.5rem;
        }

        .card-header {
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem 1rem;
        }

        .evaluate-search-bar {
            flex-direction: column;
            max-width: none;
        }

        .evaluate-search-bar .form-control {
            min-width: auto;
        }

        .table thead th,
        .table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .modal-box {
            width: 95%;
            max-height: 90vh;
            margin: 0 auto;
        }
    }

    @media (max-width: 480px) {
        .evaluate-header h2 {
            font-size: 1.25rem;
        }

        .card-header h3 {
            font-size: 1.1rem;
        }

        .evaluate-header__actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<div class="content evaluate-shell">
    <!-- Modern Header -->
    <div class="evaluate-header">
        <div class="evaluate-header-icon">
            <i class="bi bi-clipboard-check" style="font-size: 1.5rem;"></i>
        </div>
        <div class="evaluate-header-content">
            <h2>จัดการระบบประเมินผลการสอน</h2>
            <p>ระบบจัดการประเมินผลตำแหน่งทางวิชาการ</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?= count($teachinglist) ?></div>
            <div class="stat-label">ผู้ขอประเมินทั้งหมด</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-value"><?= count(array_filter($teachinglist, fn($item) => empty($item['stop_date']))) ?></div>
            <div class="stat-label">รอดำเนินการ</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?= count(array_filter($teachinglist, fn($item) => !empty($item['stop_date']))) ?></div>
            <div class="stat-label">ดำเนินการแล้ว</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="bi bi-send"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">อีเมลที่ส่งแล้ว</div>
        </div>
    </div>

    <!-- Evaluation Table Card -->
    <div class="card">
        <div class="card-header-row">
            <h2><i class="bi bi-list-ul"></i> ตารางรายชื่อ</h2>
            <div class="inline-search">
                <i class="bi bi-search"></i>
                <input type="text" id="searchEmail" placeholder="ค้นหาด้วยอีเมล..." onkeyup="if(event.key==='Enter') searchByEmail()">
            </div>
        </div>
        <div id="searchResults" style="display:none; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0;"></div>
        <div class="news-table-wrap">
            <table class="table" id="evaluationTable">
                <thead>
                    <tr>
                        <th style="width: 5%">ลำดับ</th>
                        <th style="width: 20%">ชื่อ - สกุล</th>
                        <th style="width: 12%">หลักสูตร</th>
                        <th style="width: 10%">วันที่ยื่น</th>
                        <th style="width: 10%">วันที่สิ้นสุด</th>
                        <th style="width: 8%">ระยะเวลา</th>
                        <th style="width: 10%">ตำแหน่ง</th>
                        <th style="width: 15%">รายวิชา</th>
                        <th style="width: 10%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachinglist)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--color-gray-400);"></i>
                                    <p class="mt-2 mb-0">ไม่มีรายการขอรับการประเมิน</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $index = 1;
                        foreach ($teachinglist as $rawdata):
                            $now = new DateTime();
                            if (!empty($rawdata['stop_date'])) $now = new DateTime($rawdata['stop_date']);
                            $date = new DateTime($rawdata['submit_date'] ?? 'now');
                            $days = $date->diff($now)->format('%a');
                            $statusColor = !empty($rawdata['stop_date']) ? 'emerald' : 'amber';
                            $statusText = !empty($rawdata['stop_date']) ? 'ดำเนินการแล้ว' : 'รอดำเนินการ';
                        ?>
                            <tr>
                                <td class="text-slate-500 font-medium"><?= $index ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?= mb_substr(esc($rawdata['first_name'] ?? ''), 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="user-name"><?= esc(($rawdata['first_name'] ?? '') . ' ' . ($rawdata['last_name'] ?? '')) ?></div>
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-700"><?= $statusText ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-slate-600 text-xs"><?= esc($rawdata['curriculum'] ?? '-') ?></td>
                                <td class="text-slate-600"><?= esc($rawdata['submit_date'] ?? '-') ?></td>
                                <td>
                                    <?php if (empty($rawdata['stop_date'])): ?>
                                        <div class="d-flex gap-1">
                                            <input type="date" class="form-control form-control-sm" id="enddate<?= (int)$rawdata['id'] ?>" style="width: 120px;">
                                            <button type="button" class="btn btn-success btn-sm" onclick="savedate(<?= (int)$rawdata['id'] ?>)" title="บันทึกวันที่">
                                                <i class="bi bi-check-lg"></i> <span>บันทึก</span>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?= esc($rawdata['stop_date']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-secondary"><?= $days ?> วัน</span></td>
                                <td><span class="bg-slate-50 text-slate-700 text-xs px-2 py-1 rounded-lg font-medium"><?= esc($rawdata['position_major'] ?? '-') ?></span></td>
                                <td class="text-slate-600 text-xs">
                                    <small><?= esc(($rawdata['subject_name'] ?? '') . ' (' . ($rawdata['subject_credit'] ?? '-') . ')') ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="getInfo(<?= (int)$rawdata['id'] ?>)" data-bs-toggle="modal" data-bs-target="#modalCenter" title="รายละเอียด">
                                            <i class="bi bi-info-circle"></i> <span>รายละเอียด</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="getResult(<?= (int)$rawdata['id'] ?>)" title="ผลประเมิน">
                                            <i class="bi bi-clipboard-data"></i> <span>ผลประเมิน</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(<?= (int)$rawdata['id'] ?>, '<?= esc(($rawdata['first_name'] ?? '') . ' ' . ($rawdata['last_name'] ?? '')) ?>')" title="ลบรายการ">
                                            <i class="bi bi-trash"></i> <span>ลบ</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php $index++;
                        endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal รายละเอียด -->
<div class="modal-backdrop" id="modalBackdrop" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <!-- Modal Header -->
        <div class="modal-header">
            <h3><i class="bi bi-file-earmark-text"></i> รายละเอียดการประเมิน</h3>
            <button type="button" class="modal-close" onclick="closeModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <!-- Flat Tabs -->
        <div class="flat-tabs" id="detailTabs">
            <button class="flat-tab-btn active" id="dtab-requestor" onclick="switchTab('requestor', this)">
                <i class="bi bi-person"></i> ผู้ขอรับการประเมิน
            </button>
            <button class="flat-tab-btn" id="dtab-evaluators" onclick="switchTab('evaluators', this)">
                <i class="bi bi-people"></i> ผู้ประเมิน
            </button>
        </div>
        <!-- Tab: ผู้ขอรับการประเมิน -->
        <div id="tab-requestor" class="modal-body" style="display:block;">
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-person"></i> ชื่อผู้ขอเสนอ</div>
                    <div class="info-card-value"><span id="msg_sFname"></span> <span id="msg_slname"></span></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-book"></i> หลักสูตร</div>
                    <div class="info-card-value" id="msg_scurriculum">-</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-calendar3"></i> วันที่เริ่มเข้าทำงาน</div>
                    <div class="info-card-value" id="msg_sworkingdate">-</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-briefcase"></i> ตำแหน่งที่ขอรับการประเมิน</div>
                    <div class="info-card-value" id="msg_sposition">-</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-tag"></i> สาขาที่เสนอ</div>
                    <div class="info-card-value" id="msg_spositionmajor">-</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-hash"></i> รหัสรายวิชา</div>
                    <div class="info-card-value" id="msg_ssubjectid">-</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-journal-text"></i> ชื่อรายวิชา</div>
                    <div class="info-card-value" id="msg_ssubjectname">-</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><i class="bi bi-award"></i> หน่วยกิต</div>
                    <div class="info-card-value" id="msg_ssubjectcredit">-</div>
                </div>
            </div>
            <!-- File & Video -->
            <div class="info-grid" style="margin-top: 0.75rem;">
                <div class="info-card blue" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div class="info-card-label"><i class="bi bi-file-earmark-arrow-down"></i> ไฟล์เอกสาร</div>
                        <div class="info-card-value" id="msg_slinkdoc_name">-</div>
                    </div>
                    <div id="msg_slinkdoc"></div>
                </div>
                <div class="info-card purple" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div class="info-card-label"><i class="bi bi-camera-video"></i> ลิงก์วิดีโอ</div>
                        <div class="info-card-value">วิดีโอการสอน</div>
                    </div>
                    <div id="msg_slinkvideo"></div>
                </div>
            </div>
        </div>
        <!-- Tab: ผู้ประเมิน -->
        <div id="tab-evaluators" style="display:none; flex-direction:column; flex:1; overflow:hidden;">
            <div class="modal-body" style="flex:1; overflow-y:auto;">
                <form id="frmdata">
                    <input type="hidden" id="idEvaluate" name="idEvaluate">
                    <?php
                    $evalColors = ['n1', 'n2', 'n3'];
                    for ($i = 1; $i <= 3; $i++): ?>
                        <div class="eval-card">
                            <div class="eval-card-header">
                                <div class="eval-num-badge <?= $evalColors[$i - 1] ?>"><?= $i ?></div>
                                <h4 style="margin:0; font-weight:600; color:#0369a1; font-size:0.9375rem;">ผู้ประเมินคนที่ <?= $i ?></h4>
                            </div>
                            <div class="eval-card-body">
                                <select id="ref<?= $i ?>" name="ref<?= $i ?>">
                                    <option value="">เลือกผู้ประเมิน...</option>
                                    <?php foreach ($allTeacher as $val): ?>
                                        <option value="<?= esc(strtolower($val['email'] ?? '')) ?>"><?= esc($val['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-success btn-sm" onclick="indvidualSendmail(<?= $i ?>)" style="white-space:nowrap;">
                                    <i class="bi bi-envelope"></i> ส่งอีเมล
                                </button>
                            </div>
                        </div>
                    <?php endfor; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveref()">
                    <i class="bi bi-save"></i> บันทึกข้อมูล
                </button>
                <button type="button" class="btn btn-secondary" id="genpdf">
                    <i class="bi bi-printer"></i> พิมพ์เอกสาร
                </button>
                <button type="button" class="btn btn-success" onclick="sendmail()">
                    <i class="bi bi-envelope-check"></i> ส่งอีเมลทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal ผลการประเมิน -->
<div class="modal-backdrop" id="resultBackdrop" onclick="if(event.target===this)closeResultModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-clipboard-check"></i> ผลการประเมิน</h3>
            <button type="button" class="modal-close" onclick="closeResultModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="flat-tabs" id="resultTabs">
            <button class="flat-tab-btn active" onclick="switchResultTab(1, this)">
                <i class="bi bi-person-check"></i> ผู้ประเมินคนที่ 1
            </button>
            <button class="flat-tab-btn" onclick="switchResultTab(2, this)">
                <i class="bi bi-person-check"></i> ผู้ประเมินคนที่ 2
            </button>
            <button class="flat-tab-btn" onclick="switchResultTab(3, this)">
                <i class="bi bi-person-check"></i> ผู้ประเมินคนที่ 3
            </button>
        </div>
        <div class="modal-body">
            <div id="navs1" class="result-tab"></div>
            <div id="navs2" class="result-tab" style="display:none;"></div>
            <div id="navs3" class="result-tab" style="display:none;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="genPDFresult()">
                <i class="bi bi-printer"></i> พิมพ์ผลการประเมิน
            </button>
        </div>
    </div>
</div>

<!-- Modal Confirm -->
<div class="modal-backdrop" id="confirmBackdrop" onclick="if(event.target===this)closeConfirmModal()">
    <div class="modal-box" style="max-width: 400px;">
        <div class="modal-header">
            <h3><i class="bi bi-check-circle"></i> แจ้งเตือน</h3>
            <button type="button" class="modal-close" onclick="closeConfirmModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-body text-center" id="messageData"></div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-primary" onclick="closeConfirmModal()"><span>ตกลง</span></button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $base ?>pdfmake/build/pdfmake.min.js"></script>
<script src="<?= $base ?>pdfmake/build/vfs_fonts.js"></script>
<script src="<?= $base ?>pdfmake/EvaluateDocument.js"></script>
<script>
    var global_id = 0;
    var adminBase = <?= json_encode($adminBase) ?>;
    var baseUrl = <?= json_encode($base) ?>;

    // Modal functions
    function openModal(backdropId) {
        document.getElementById(backdropId).classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('modalBackdrop').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function closeResultModal() {
        document.getElementById('resultBackdrop').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function closeConfirmModal() {
        document.getElementById('confirmBackdrop').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function showConfirm(message) {
        document.getElementById('messageData').innerHTML = message;
        openModal('confirmBackdrop');
    }

    // Tab functions
    function switchTab(tabName, btn) {
        document.querySelectorAll('#detailTabs .flat-tab-btn').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        var reqEl = document.getElementById('tab-requestor');
        var evalEl = document.getElementById('tab-evaluators');
        if (tabName === 'requestor') {
            reqEl.style.display = 'block';
            evalEl.style.display = 'none';
        } else {
            reqEl.style.display = 'none';
            evalEl.style.display = 'flex';
        }
    }

    function switchResultTab(num, btn) {
        document.querySelectorAll('#resultTabs .flat-tab-btn').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.result-tab').forEach((el, idx) => {
            el.style.display = (idx + 1) === num ? 'block' : 'none';
        });
    }

    function savedate(id) {
        var stopdate = document.getElementById('enddate' + id).value;
        if (!stopdate) {
            showConfirm('<div class="alert alert-warning">กรุณาระบุวันที่</div>');
            return;
        }
        fetch(adminBase + '/saveDate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id + '&stopdate=' + encodeURIComponent(stopdate)
        }).then(() => {
            showConfirm('<div class="alert alert-success">บันทึกเรียบร้อย</div>');
            setTimeout(() => location.reload(), 1500);
        });
    }

    function getInfo(id) {
        global_id = id;
        ['ref1', 'ref2', 'ref3'].forEach(ref => document.getElementById(ref).value = '');

        fetch(adminBase + '/getEvaluateInfo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(r => r.json())
            .then(data => {
                var url = baseUrl + 'serve/uploads/documents/';
                document.getElementById('msg_sFname').textContent = data.first_name || '';
                document.getElementById('msg_slname').textContent = data.last_name || '';
                document.getElementById('msg_scurriculum').textContent = data.curriculum || '';
                document.getElementById('msg_sworkingdate').textContent = data.start_date || '';
                document.getElementById('msg_sposition').textContent = data.position || '';
                document.getElementById('msg_spositionmajor').textContent = data.position_major || '';
                document.getElementById('msg_ssubjectid').textContent = data.subject_id || '';
                document.getElementById('msg_ssubjectname').textContent = data.subject_name || '';
                document.getElementById('msg_ssubjectcredit').textContent = data.subject_credit || '';
                var docName = data.file_doc ? data.file_doc.split('/').pop() : '-';
                document.getElementById('msg_slinkdoc_name').textContent = docName;
                document.getElementById('msg_slinkdoc').innerHTML = data.file_doc ?
                    '<a href="' + url + data.file_doc + '" target="_blank" class="btn btn-sm btn-primary" style="white-space:nowrap;"><i class="bi bi-download"></i> ดาวน์โหลด</a>' : '';
                document.getElementById('msg_slinkvideo').innerHTML = data.link_video ?
                    '<a href="' + data.link_video + '" target="_blank" class="btn btn-sm btn-secondary" style="white-space:nowrap;"><i class="bi bi-play-circle"></i> เปิดวีดีโอ</a>' : '';
                document.getElementById('idEvaluate').value = id;

                if (data.referees && data.referees.length) {
                    data.referees.forEach(function(ref) {
                        var refNum = ref.ref_num || ref.refnum;
                        var refEl = document.getElementById('ref' + refNum);
                        if (refEl) refEl.value = (ref.email || '').toLowerCase();
                    });
                }
                openModal('modalBackdrop');
            });
    }

    function getResult(id) {
        global_id = id;
        var urlfile = baseUrl + 'serve/uploads/';
        var urllink = baseUrl + 'evaluate/';

        fetch(adminBase + '/getResult', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(r => r.json())
            .then(data => {
                ['navs1', 'navs2', 'navs3'].forEach(el => document.getElementById(el).innerHTML = '');

                if (data.referees && data.referees.length) {
                    data.referees.forEach(function(ref, i) {
                        var payload = btoa(JSON.stringify({
                            id: id,
                            email: ref.email
                        }));
                        var html = '<div class="info-grid" style="margin-bottom:0.75rem;">';
                        html += '<div class="info-card"><div class="info-card-label"><i class="bi bi-person"></i> ชื่อผู้ประเมิน</div><div class="info-card-value">' + (ref.name || '-') + '</div></div>';
                        html += '<div class="info-card"><div class="info-card-label"><i class="bi bi-envelope"></i> อีเมล</div><div class="info-card-value">' + (ref.email || '-') + '</div></div>';
                        html += '<div class="info-card blue"><div class="info-card-label"><i class="bi bi-bar-chart"></i> ผลการประเมิน</div><div class="info-card-value">' + (ref.score || '<span style="color:#94a3b8">ยังไม่ประเมิน</span>') + '</div></div>';
                        html += '<div class="info-card" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">';
                        if (ref.file_doc) html += '<a href="' + urlfile + ref.file_doc + '" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-download"></i> ดาวน์โหลดไฟล์</a>';
                        html += '<a href="' + urllink + payload + '" target="_blank" class="btn btn-sm btn-secondary"><i class="bi bi-box-arrow-up-right"></i> เปิดแบบประเมิน</a>';
                        html += '</div></div>';
                        if (ref.comment) html += '<div class="info-card" style="grid-column:1/-1;"><div class="info-card-label"><i class="bi bi-chat-text"></i> ข้อเสนอแนะ</div><div class="info-card-value" style="font-weight:400;">' + ref.comment + '</div></div>';
                        document.getElementById('navs' + (i + 1)).innerHTML = html;
                    });
                } else {
                    document.getElementById('navs1').innerHTML = '<div class="empty-state" style="text-align:center;padding:2rem;"><i class="bi bi-inbox" style="font-size:3rem;color:#94a3b8;"></i><p style="color:#64748b;margin-top:0.5rem;">ยังไม่มีผลการประเมิน</p></div>';
                }
                openModal('resultBackdrop');
            });
    }

    function saveref() {
        var obj = {
            idEvaluate: document.getElementById('idEvaluate').value,
            ref1: document.getElementById('ref1').value,
            ref2: document.getElementById('ref2').value,
            ref3: document.getElementById('ref3').value,
            nameref1: document.getElementById('ref1').options[document.getElementById('ref1').selectedIndex].text,
            nameref2: document.getElementById('ref2').options[document.getElementById('ref2').selectedIndex].text,
            nameref3: document.getElementById('ref3').options[document.getElementById('ref3').selectedIndex].text
        };
        fetch(adminBase + '/printRefAndSave', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(obj)
            })
            .then(r => r.json())
            .then(() => {
                showConfirm('<div class="alert alert-success">บันทึกข้อมูลเรียบร้อย</div>');
            });
    }

    function indvidualSendmail(num) {
        var select = document.getElementById('ref' + num);
        var email = select.value;
        var name = select.options[select.selectedIndex].text;
        var id = document.getElementById('idEvaluate').value;

        if (!email) {
            showConfirm('<div class="alert alert-warning">กรุณาเลือกผู้ประเมิน</div>');
            return;
        }

        fetch(adminBase + '/sendmailEvaluate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'name=' + encodeURIComponent(name) + '&mail=' + encodeURIComponent(email) + '&id=' + id + '&refnum=' + num
            })
            .then(() => {
                showConfirm('<div class="alert alert-success">ส่งอีเมลเรียบร้อย</div>');
            });
    }

    function sendmail() {
        var count = 0;
        var id = document.getElementById('idEvaluate').value;

        for (var num = 1; num <= 3; num++) {
            var select = document.getElementById('ref' + num);
            var email = select.value;
            var name = select.options[select.selectedIndex].text;

            if (email) {
                fetch(adminBase + '/sendmailEvaluate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&mail=' + encodeURIComponent(email) + '&id=' + id + '&refnum=' + num
                });
                count++;
            }
        }
        showConfirm('<div class="alert alert-success">ส่งอีเมลไปยังผู้ประเมิน ' + count + ' คน เรียบร้อย</div>');
    }

    document.getElementById('genpdf').addEventListener('click', function() {
        fetch(adminBase + '/getEvaluateInfo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + global_id
            })
            .then(r => r.json())
            .then(data => {
                var name = (data.title_thai || '') + ' ' + (data.first_name || '') + ' ' + (data.last_name || '');
                var title = data.position === 'ผู้ช่วยศาสตราจารย์' ? 'อาจารย์' : 'ผู้ช่วยศาสตราจารย์';
                var dataname = [name, data.curriculum || '', data.start_date || '', data.position || '', data.position_major || '', data.position_major_id || '', title];
                var datasubject = [data.subject_id || '', data.subject_name || '', data.subject_credit || '', data.subject_teacher || '', data.subject_detail || ''];
                var dataref = data.referees || [];

                if (typeof makepdf === 'function') {
                    makepdf(dataname, datasubject, dataref);
                } else {
                    showConfirm('<div class="alert alert-danger">ไม่พบฟังก์ชัน makepdf</div>');
                }
            });
    });

    function genPDFresult() {
        if (typeof makePDFResult === 'function') {
            makePDFResult(global_id);
        } else {
            showConfirm('<div class="alert alert-danger">ไม่พบฟังก์ชัน makePDFResult</div>');
        }
    }

    // Search by email function
    function searchByEmail() {
        var email = document.getElementById('searchEmail').value.trim();
        if (!email) {
            showConfirm('<div class="alert alert-warning">กรุณาระบุอีเมล</div>');
            return;
        }

        var resultsDiv = document.getElementById('searchResults');
        resultsDiv.innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> กำลังค้นหา...</div>';

        fetch(adminBase + '/search?email=' + encodeURIComponent(email))
            .then(r => r.json())
            .then(data => {
                if (!data.success || data.count === 0) {
                    resultsDiv.innerHTML = '<div class="alert alert-info">ไม่พบข้อมูลการประเมินสำหรับอีเมลนี้</div>';
                    return;
                }

                var html = '<div class="alert alert-success">พบ ' + data.count + ' รายการ</div>';
                html += '<div class="table-responsive"><table class="table table-sm table-hover">';
                html += '<thead><tr><th>ชื่อ-สกุล</th><th>ตำแหน่ง</th><th>รายวิชา</th><th>วันที่ยื่น</th><th>ผู้ประเมิน</th><th>ผลการประเมิน</th></tr></thead><tbody>';

                data.data.forEach(function(eval) {
                    var refereeCount = eval.referees ? eval.referees.length : 0;
                    var completedCount = eval.referees ? eval.referees.filter(function(r) {
                        return r.score && r.score !== '';
                    }).length : 0;
                    var statusText = completedCount + '/' + refereeCount + ' คน';
                    var statusClass = completedCount === refereeCount && refereeCount > 0 ? 'badge-success' : 'badge-warning';

                    html += '<tr>';
                    html += '<td>' + (eval.first_name || '') + ' ' + (eval.last_name || '') + '</td>';
                    html += '<td>' + (eval.position_major || '-') + '</td>';
                    html += '<td>' + (eval.subject_name || '-') + '</td>';
                    html += '<td>' + (eval.submit_date || '-') + '</td>';
                    html += '<td><span class="badge ' + statusClass + '">' + statusText + '</span></td>';
                    html += '<td>';
                    if (eval.referees && eval.referees.length > 0) {
                        eval.referees.forEach(function(ref, idx) {
                            if (ref.score) {
                                html += '<small>ผู้ประเมิน ' + (idx + 1) + ': ' + ref.score + '</small><br>';
                            }
                        });
                    } else {
                        html += '<small class="text-muted">-</small>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                resultsDiv.innerHTML = html;
            })
            .catch(function(err) {
                resultsDiv.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการค้นหา</div>';
                console.error('Search error:', err);
            });
    }

    // Delete record function with SweetAlert confirmation
    function deleteRecord(id, name) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            html: 'คุณต้องการลบรายการของ <strong>' + (name || 'รายการนี้') + '</strong> ใช่หรือไม่?<br><span style="color:#dc3545;font-size:0.875rem;">การลบไม่สามารถเรียกคืนได้</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบรายการ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(adminBase + '/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + id
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: data.message || 'ลบรายการเรียบร้อยแล้ว',
                                confirmButtonText: 'ตกลง'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message || 'ไม่สามารถลบรายการได้',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                            confirmButtonText: 'ตกลง'
                        });
                    });
            }
        });
    }
</script>
<?= $this->endSection() ?>