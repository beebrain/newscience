<?php
$pageTitle = 'จัดการผู้สมัคร — สัปดาห์วิทยาศาสตร์ 2569';

// คำนวณ stat cards
$totalAll = 0;
foreach ($summary as $key => $levels) {
    foreach ($levels as $s) { $totalAll += $s['count']; }
}
$activeComp = isset($competitions[$compKey]) ? $competitions[$compKey] : null;
$activeTotal = 0;
if ($activeComp) {
    foreach ($summary[$compKey] as $s) { $activeTotal += $s['count']; }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --nav-bg:    #0d1b3e;
            --nav-hover: rgba(255,255,255,.08);
            --nav-act:   rgba(99,179,237,.18);
            --nav-act-b: #63b3ed;
            --acc-teal:  #00bfa5;
            --acc-blue:  #3b82f6;
            --acc-red:   #ef4444;
            --acc-amber: #f59e0b;
            --bg:        #f1f5f9;
            --card:      #ffffff;
            --border:    #e2e8f0;
            --text:      #1e293b;
            --muted:     #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }

        body {
            font-family: 'Sarabun', 'Inter', Tahoma, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Shell ── */
        .dash-shell { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .dash-nav {
            width: 240px;
            min-width: 240px;
            background: var(--nav-bg);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .dash-nav::-webkit-scrollbar { width: 4px; }
        .dash-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

        .dash-brand {
            padding: 1.1rem 1rem .9rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .dash-brand .badge-sci {
            display: inline-flex; align-items: center; gap: .3rem;
            background: rgba(0,191,165,.2);
            border: 1px solid rgba(0,191,165,.35);
            color: #7fffd4;
            border-radius: 12px;
            padding: .12rem .6rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: .5rem;
        }
        .dash-brand .title {
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            line-height: 1.35;
        }
        .dash-brand .subtitle {
            color: rgba(255,255,255,.4);
            font-size: .72rem;
            margin-top: .2rem;
        }

        .nav-section-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            padding: .9rem 1rem .35rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .42rem 1rem;
            color: rgba(255,255,255,.6);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            border-left: 2px solid transparent;
            transition: background .12s, color .12s, border-color .12s;
            cursor: pointer;
        }
        .nav-item:hover { background: var(--nav-hover); color: #fff; }
        .nav-item.active {
            background: var(--nav-act);
            color: #fff;
            border-left-color: var(--nav-act-b);
        }
        .nav-item .count-chip {
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.7);
            border-radius: 10px;
            padding: .06rem .45rem;
            font-size: .72rem;
            font-weight: 600;
        }
        .nav-item.active .count-chip { background: rgba(99,179,237,.25); color: #90cdf4; }

        .nav-sub-item {
            padding: .32rem 1rem .32rem 2.2rem;
            font-size: .8rem;
            border-left-width: 2px;
        }
        .nav-sub-item::before {
            content: '—';
            color: rgba(255,255,255,.2);
            margin-right: .4rem;
            font-size: .65rem;
        }

        .nav-divider { height: 1px; background: rgba(255,255,255,.07); margin: .6rem 0; }

        .nav-bottom {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,.07);
            padding: .5rem 0;
        }

        /* ── Main ── */
        .dash-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        /* Topbar */
        .dash-topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .dash-topbar .page-title { font-weight: 700; font-size: 1rem; color: var(--text); }
        .dash-topbar .page-sub { font-size: .78rem; color: var(--muted); }

        /* Content */
        .dash-content { flex: 1; padding: 1.5rem; }

        /* Stat cards */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.1rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }
        .stat-card .stat-label { font-size: .75rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1; color: var(--text); font-family: 'Inter', sans-serif; }
        .stat-card .stat-sub { font-size: .75rem; color: var(--muted); }
        .stat-card.accent-teal .stat-value { color: var(--acc-teal); }
        .stat-card.accent-blue .stat-value { color: var(--acc-blue); }

        /* Toolbar */
        .dash-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .dash-toolbar .toolbar-title { font-weight: 700; font-size: 1rem; }
        .dash-toolbar .toolbar-sub { font-size: .78rem; color: var(--muted); }

        /* Table */
        .reg-table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        .reg-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .855rem;
        }
        .reg-table thead th {
            background: #f8fafc;
            border-bottom: 1.5px solid var(--border);
            padding: .65rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }
        .reg-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }
        .reg-table tbody tr:last-child { border-bottom: 0; }
        .reg-table tbody tr:hover { background: #f8fafc; }
        .reg-table tbody tr.is-deleted { opacity: .45; }
        .reg-table tbody td { padding: .7rem 1rem; vertical-align: middle; }

        .school-name { font-weight: 600; color: var(--text); }
        .school-date { font-size: .75rem; color: var(--muted); }

        /* Pills */
        .pill {
            display: inline-flex; align-items: center;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .pill-level  { background: #eff6ff; color: #1d4ed8; }
        .pill-main   { background: #e0f2f1; color: #00695c; }
        .pill-res    { background: #f1f5f9; color: var(--muted); }
        .pill-active { background: #dcfce7; color: #166534; }
        .pill-del    { background: #fef2f2; color: #991b1b; }
        .pill-del::before { content: '✕ '; }

        /* Action buttons */
        .btn-view {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .3rem .75rem;
            border: 1.5px solid var(--border);
            border-radius: 7px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--acc-blue);
            background: #eff6ff;
            text-decoration: none;
            transition: background .12s, border-color .12s;
        }
        .btn-view:hover { background: #dbeafe; border-color: var(--acc-blue); color: var(--acc-blue); }
        .btn-del {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .3rem .7rem;
            border: 1.5px solid #fee2e2;
            border-radius: 7px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--acc-red);
            background: #fff5f5;
            cursor: pointer;
            transition: background .12s, border-color .12s;
        }
        .btn-del:hover { background: #fee2e2; border-color: var(--acc-red); }

        /* Toggle deleted */
        .btn-toggle {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .38rem .9rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            background: var(--card);
            text-decoration: none;
            transition: border-color .12s, color .12s;
        }
        .btn-toggle:hover { border-color: var(--acc-blue); color: var(--acc-blue); }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3.5rem 2rem;
            color: var(--muted);
        }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: .75rem; }
        .empty-state p { font-size: .9rem; }

        /* Alert */
        .dash-alert {
            border-radius: 10px;
            border: 0;
            padding: .75rem 1.1rem;
            margin-bottom: 1rem;
            font-size: .875rem;
        }
        .dash-alert-danger { background: #fef2f2; color: #991b1b; }
        .dash-alert-success { background: #f0fdf4; color: #166534; }

        /* Modal */
        .modal-content { border: 0; border-radius: 14px; box-shadow: 0 20px 60px rgba(0,0,0,.18); }
        .modal-header { border-radius: 14px 14px 0 0; border-bottom: 1px solid var(--border); padding: 1rem 1.25rem; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: .75rem 1.25rem; }
        .modal-school-name { font-weight: 700; color: var(--text); }
    </style>
</head>
<body>
<div class="dash-shell">

    <!-- ── Sidebar ── -->
    <nav class="dash-nav">
        <div class="dash-brand">
            <div class="badge-sci">🔬 Admin Panel</div>
            <div class="title">สัปดาห์วิทยาศาสตร์ 2569</div>
            <div class="subtitle">จัดการผู้สมัครแข่งขัน</div>
        </div>

        <div class="nav-section-label">รายการแข่งขัน</div>

        <?php foreach ($competitions as $key => $comp):
            $tot = 0;
            foreach ($summary[$key] as $s) { $tot += $s['count']; }
        ?>
            <a href="<?= base_url('scienceweek/manage?competition='.$key) ?>"
               class="nav-item <?= $compKey === $key ? 'active' : '' ?>">
                <span><?= esc($comp['name_th']) ?></span>
                <span class="count-chip"><?= $tot ?></span>
            </a>
            <?php if ($compKey === $key): ?>
                <?php foreach ($comp['levels'] as $lk => $lv): ?>
                    <a href="<?= base_url("scienceweek/manage?competition={$key}&level={$lk}") ?>"
                       class="nav-item nav-sub-item <?= $levelKey === $lk ? 'active' : '' ?>">
                        <span><?= esc($lv) ?></span>
                        <span class="count-chip">
                            <?= $summary[$key][$lk]['count'] ?>
                            <?= $summary[$key][$lk]['cap'] !== null ? '/'.$summary[$key][$lk]['cap'] : '' ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="nav-divider"></div>
        <div class="nav-bottom">
            <a href="<?= base_url('scienceweek/verify') ?>" class="nav-item">
                <span>🔍 หน้าตรวจสอบ (public)</span>
            </a>
            <a href="<?= base_url('scienceweek') ?>" class="nav-item">
                <span>🏠 หน้าสมัคร</span>
            </a>
            <div class="nav-divider"></div>
            <?php
            $swRole = session('admin_role') ?? 'user';
            $swIsAdminRole = in_array($swRole, ['admin', 'editor', 'super_admin', 'faculty_admin'], true);
            ?>
            <a href="<?= base_url($swIsAdminRole ? 'admin' : 'dashboard') ?>" class="nav-item">
                <span><?= $swIsAdminRole ? '← กลับ Admin' : '← กลับ Dashboard' ?></span>
            </a>
        </div>
    </nav>

    <!-- ── Main ── -->
    <div class="dash-main">

        <!-- Topbar -->
        <div class="dash-topbar">
            <div>
                <div class="page-title">
                    <?php if ($activeComp): ?>
                        <?= esc($activeComp['name_th']) ?>
                    <?php else: ?>
                        ภาพรวมทั้งหมด
                    <?php endif; ?>
                </div>
                <div class="page-sub">งานสัปดาห์วิทยาศาสตร์แห่งชาติ ส่วนภูมิภาค ประจำปี 2569</div>
            </div>
        </div>

        <div class="dash-content">

            <!-- Alerts -->
            <?php if (session()->has('error')): ?>
                <div class="dash-alert dash-alert-danger">⚠️ <?= esc(session('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->has('success')): ?>
                <div class="dash-alert dash-alert-success">✓ <?= esc(session('success')) ?></div>
            <?php endif; ?>

            <!-- Stat cards -->
            <div class="stat-grid">
                <div class="stat-card accent-teal">
                    <div class="stat-label">ผู้สมัครทั้งหมด</div>
                    <div class="stat-value"><?= $totalAll ?></div>
                    <div class="stat-sub">ทุกรายการแข่งขัน</div>
                </div>
                <?php if ($activeComp): ?>
                <div class="stat-card accent-blue">
                    <div class="stat-label">รายการที่เลือก</div>
                    <div class="stat-value"><?= $activeTotal ?></div>
                    <div class="stat-sub">
                        <?= esc($levelKey !== '' ? ($activeComp['levels'][$levelKey] ?? 'ระดับที่เลือก') : 'ทุกระดับ') ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php foreach ($summary as $key => $levels):
                    $tot = 0; foreach ($levels as $s) $tot += $s['count'];
                    $comp2 = $competitions[$key];
                    $capTotal = $comp2['cap_total'];
                ?>
                <div class="stat-card">
                    <div class="stat-label" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= esc($comp2['name_th']) ?></div>
                    <div class="stat-value" style="font-size:1.4rem;"><?= $tot ?></div>
                    <div class="stat-sub">
                        <?php if ($capTotal !== null): ?>
                            <span style="color:<?= $tot >= $capTotal ? 'var(--acc-red)' : 'var(--muted)'; ?>"><?= $tot ?>/<?= $capTotal ?> ทีม</span>
                        <?php else: ?>
                            <?php foreach ($levels as $lk => $s):
                                if ($s['cap'] !== null): ?>
                                    <?= esc($competitions[$key]['levels'][$lk] ?? $lk) ?>: <?= $s['count'] ?>/<?= $s['cap'] ?><br>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            &nbsp;
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Table or empty -->
            <?php if ($compKey === '' || !isset($competitions[$compKey])): ?>
                <div class="reg-table-wrap">
                    <div class="empty-state">
                        <div class="icon">📋</div>
                        <p>เลือกรายการแข่งขันจากเมนูด้านซ้าย</p>
                    </div>
                </div>

            <?php else:
                $comp = $competitions[$compKey];
                $levelLabel = $levelKey !== '' ? ($comp['levels'][$levelKey] ?? '') : 'ทุกระดับ';
            ?>
                <div class="dash-toolbar">
                    <div>
                        <div class="toolbar-title"><?= esc($comp['name_th']) ?></div>
                        <div class="toolbar-sub"><?= esc($levelLabel) ?> · <?= count($registrations) ?> รายการ</div>
                    </div>
                    <a href="?competition=<?= esc($compKey) ?>&level=<?= esc($levelKey) ?>&show_deleted=<?= $showDeleted ? '0' : '1' ?>"
                       class="btn-toggle">
                        <?= $showDeleted ? '🙈 ซ่อนรายการที่ลบ' : '👁 แสดงรายการที่ถูกลบ' ?>
                    </a>
                </div>

                <div class="reg-table-wrap">
                    <?php if (empty($registrations)): ?>
                        <div class="empty-state">
                            <div class="icon">🔎</div>
                            <p>ยังไม่มีผู้สมัครในหมวดนี้</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                        <table class="reg-table">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>โรงเรียน / สถาบัน</th>
                                    <th>ระดับ</th>
                                    <th>ชื่อทีม</th>
                                    <th>สมาชิก</th>
                                    <th>อาจารย์ผู้ควบคุม</th>
                                    <th>วันที่สมัคร</th>
                                    <th>สถานะ</th>
                                    <th style="width:90px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($registrations as $i => $reg):
                                $isDeleted = !empty($reg['deleted_at']);
                                $parts = $participants[$reg['id']] ?? [];
                                $mainCount = count(array_filter($parts, fn($p) => $p['role']==='main'));
                                $resCount  = count(array_filter($parts, fn($p) => $p['role']==='reserve'));
                            ?>
                                <tr class="<?= $isDeleted ? 'is-deleted' : '' ?>">
                                    <td style="color:var(--muted);font-size:.78rem;"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="school-name"><?= esc($reg['school_name']) ?></div>
                                        <?php if ($reg['contact_phone']): ?>
                                            <div class="school-date"><?= esc($reg['contact_phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="pill pill-level">
                                            <?= esc($comp['levels'][$reg['level_key']] ?? $reg['level_key']) ?>
                                        </span>
                                    </td>
                                    <td style="color:<?= $reg['team_name'] ? 'var(--text)' : 'var(--muted)'; ?>">
                                        <?= $reg['team_name'] ? esc($reg['team_name']) : '—' ?>
                                    </td>
                                    <td>
                                        <span class="pill pill-main"><?= $mainCount ?> คน</span>
                                        <?php if ($resCount > 0): ?>
                                            <span class="pill pill-res ms-1">+<?= $resCount ?> สำรอง</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($reg['coach_name']) ?></td>
                                    <td>
                                        <div style="font-size:.8rem;white-space:nowrap;">
                                            <?= date('d/m/Y', strtotime($reg['created_at'])) ?>
                                        </div>
                                        <div class="school-date"><?= date('H:i', strtotime($reg['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($isDeleted): ?>
                                            <span class="pill pill-del">ลบแล้ว</span>
                                        <?php else: ?>
                                            <span class="pill pill-active">● ปกติ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= base_url('scienceweek/manage/'.$reg['id']) ?>"
                                               class="btn-view">ดู</a>
                                            <?php if (!$isDeleted): ?>
                                                <button type="button" class="btn-del"
                                                        onclick="confirmDelete(<?= $reg['id'] ?>, '<?= esc($reg['school_name'], 'js') ?>')">
                                                    ลบ
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div><!-- /.dash-content -->
    </div><!-- /.dash-main -->
</div><!-- /.dash-shell -->

<!-- ── Delete Modal ── -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700" style="font-size:.95rem;">ยืนยันการลบข้อมูล</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1" style="font-size:.9rem;">
                    ต้องการลบข้อมูลการสมัครของ
                    <span class="modal-school-name" id="schoolNameDisplay"></span> ใช่หรือไม่?
                </p>
                <p class="text-muted" style="font-size:.8rem;margin:0;">
                    ข้อมูลจะถูก soft-delete (ซ่อน) ไม่ได้ลบถาวร สามารถดูได้จากตัวเลือก "แสดงรายการที่ถูกลบ"
                </p>
            </div>
            <div class="modal-footer" style="gap:.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                <form id="deleteForm" method="post" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-sm">ยืนยัน ลบ</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id, schoolName) {
    document.getElementById('schoolNameDisplay').textContent = schoolName;
    document.getElementById('deleteForm').action = '<?= base_url('scienceweek/manage/') ?>' + id + '/delete';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>
