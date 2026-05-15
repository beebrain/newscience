<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กล่องเอกสาร — E-Document</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #f6f8fc; color: #202124; }

        /* Layout */
        #inbox-shell { display: flex; height: 100vh; flex-direction: column; }
        #top-bar { height: 64px; flex-shrink: 0; background: #fff; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 16px; padding: 0 16px; z-index: 20; }
        #main-area { display: flex; flex: 1; overflow: hidden; }
        #sidebar { width: 256px; flex-shrink: 0; overflow-y: auto; padding: 8px 0 24px; background: #f6f8fc; }
        #content-area { flex: 1; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
        #category-tabs { border-bottom: 1px solid #e0e0e0; display: flex; background: #fff; flex-shrink: 0; overflow-x: auto; }
        #doc-list-wrap { flex: 1; overflow-y: auto; min-height: 0; }

        /* Sidebar nav items */
        .sidebar-nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 6px 16px 6px 20px; border-radius: 0 100px 100px 0;
            margin-right: 16px; cursor: pointer; font-size: 0.875rem;
            color: #202124; font-weight: 500; transition: background 0.15s;
            user-select: none;
        }
        .sidebar-nav-item:hover { background: #e8eaed; }
        .sidebar-nav-item.active { background: #d3e3fd; font-weight: 700; }
        .sidebar-nav-item .count-badge {
            margin-left: auto; font-size: 0.75rem; font-weight: 700; color: #202124;
        }
        .sidebar-section-title {
            font-size: 0.6875rem; font-weight: 700; color: #444746;
            letter-spacing: 0.08em; text-transform: uppercase;
            padding: 16px 20px 4px; margin-top: 8px;
        }
        .sidebar-label-item {
            display: flex; align-items: center; gap: 10px;
            padding: 5px 16px 5px 20px; border-radius: 0 100px 100px 0;
            margin-right: 16px; cursor: pointer; font-size: 0.875rem;
            color: #202124; transition: background 0.15s;
        }
        .sidebar-label-item:hover { background: #e8eaed; }
        .sidebar-label-item.active { background: #d3e3fd; font-weight: 700; }
        .label-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* Category tabs */
        .cat-tab {
            padding: 14px 24px; border-bottom: 3px solid transparent;
            font-size: 0.875rem; font-weight: 500; color: #5f6368;
            cursor: pointer; white-space: nowrap; transition: all 0.15s;
            display: flex; align-items: center; gap: 8px;
        }
        .cat-tab:hover { background: #f1f3f4; color: #202124; }
        .cat-tab.active { border-bottom-color: #1a73e8; color: #1a73e8; font-weight: 700; }

        /* Doc row */
        .doc-row {
            display: flex; align-items: center; gap: 0;
            padding: 0 16px; height: 52px; border-bottom: 1px solid #f1f3f4;
            cursor: pointer; transition: background 0.1s; position: relative;
        }
        .doc-row:hover { background: #f1f3f4; box-shadow: 0 1px 2px rgba(0,0,0,.1); z-index: 1; }
        .doc-row.unread { background: #fff; }
        .doc-row.unread .doc-title-text { font-weight: 700; }
        .doc-row.read { background: #f6f8fc; }
        .doc-row.read .doc-title-text { font-weight: 400; }

        .doc-row .chk { width: 20px; flex-shrink: 0; margin-right: 8px; }
        .doc-row .star-btn { width: 24px; flex-shrink: 0; margin-right: 8px; color: #ccc; cursor: pointer; font-size: 1rem; }
        .doc-row .star-btn.starred { color: #f6b900; }
        .doc-row .sender-col { width: 180px; flex-shrink: 0; font-size: 0.8125rem; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; padding-right: 12px; }
        .doc-row .subject-col { flex: 1; overflow: hidden; white-space: nowrap; display: flex; align-items: center; gap: 8px; min-width: 0; }
        .doc-row .doctype-badge {
            flex-shrink: 0; padding: 2px 8px; border-radius: 12px;
            font-size: 0.6875rem; font-weight: 600; background: #e8f0fe; color: #1a73e8;
        }
        .doc-row .label-chip {
            flex-shrink: 0; padding: 1px 7px; border-radius: 10px;
            font-size: 0.6875rem; font-weight: 600; color: #fff; max-width: 80px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .doc-row .date-col { width: 80px; flex-shrink: 0; text-align: right; font-size: 0.75rem; color: #5f6368; }

        /* Action bar (toolbar) */
        #toolbar { height: 48px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 4px; padding: 0 16px; background: #fff; flex-shrink: 0; }
        .toolbar-btn { padding: 8px 12px; border-radius: 4px; font-size: 0.8125rem; color: #444; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.15s; }
        .toolbar-btn:hover { background: #e8eaed; }

        /* Empty state */
        #empty-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #80868b; }

        /* Search */
        #search-input { flex: 1; max-width: 640px; height: 44px; background: #eaf1fb; border: none; border-radius: 22px; padding: 0 20px; font-size: 1rem; font-family: 'Sarabun',sans-serif; outline: none; transition: background 0.2s, box-shadow 0.2s; }
        #search-input:focus { background: #fff; box-shadow: 0 1px 6px rgba(32,33,36,.28); }

        /* Pagination */
        #pagination { display: flex; align-items: center; justify-content: flex-end; padding: 12px 16px; font-size: 0.8125rem; color: #5f6368; gap: 8px; flex-shrink: 0; border-top: 1px solid #e0e0e0; }
        .page-btn { padding: 6px 10px; border-radius: 50%; cursor: pointer; transition: background 0.15s; }
        .page-btn:hover { background: #e8eaed; }
        .page-btn.disabled { opacity: 0.4; pointer-events: none; }

        /* Forward modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 100; display: none; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #fff; border-radius: 8px; box-shadow: 0 8px 40px rgba(0,0,0,.2); width: 480px; max-width: 95vw; }
        .modal-header { padding: 16px 20px 12px; border-bottom: 1px solid #e0e0e0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; justify-content: space-between; }
        .modal-body { padding: 16px 20px; }
        .modal-footer { padding: 12px 20px; border-top: 1px solid #e0e0e0; display: flex; justify-content: flex-end; gap: 8px; }

        .tag-input-wrap { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; border: 1px solid #dadce0; border-radius: 4px; min-height: 44px; cursor: text; }
        .tag-item { display: flex; align-items: center; gap: 4px; background: #e8f0fe; color: #1a73e8; border-radius: 100px; padding: 3px 10px; font-size: 0.8125rem; }
        .tag-item button { background: none; border: none; color: #1a73e8; cursor: pointer; padding: 0; font-size: 0.75rem; }
        .tag-text-input { border: none; outline: none; font-family: 'Sarabun',sans-serif; font-size: 0.875rem; flex: 1; min-width: 140px; }
        .suggest-dropdown { position: absolute; background: #fff; border: 1px solid #dadce0; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,.15); width: 100%; max-height: 200px; overflow-y: auto; z-index: 10; }
        .suggest-item { padding: 8px 12px; cursor: pointer; font-size: 0.875rem; }
        .suggest-item:hover { background: #f1f3f4; }

        /* Label manage modal */
        .color-swatch { width: 24px; height: 24px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; flex-shrink: 0; transition: border-color 0.15s; }
        .color-swatch.selected { border-color: #202124; }

        /* Loading spinner */
        .spinner { border: 3px solid #e0e0e0; border-top-color: #1a73e8; border-radius: 50%; width: 32px; height: 32px; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #loading-wrap { display: flex; align-items: center; justify-content: center; padding: 48px; }

        /* Hamburger / mobile menu */
        #hamburger-btn { display: none; padding: 8px; border-radius: 50%; cursor: pointer; transition: background 0.15s; }
        #hamburger-btn:hover { background: #e8eaed; }
        #sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 40; }
        #sidebar-backdrop.open { display: block; }

        /* ============== MOBILE (<=768px) ============== */
        @media (max-width: 768px) {
            #hamburger-btn { display: flex; align-items: center; justify-content: center; }
            #top-bar { padding: 0 12px; gap: 8px; }
            #top-bar > a.text-xl { font-size: 1rem; }
            #top-bar > a.text-xl i { font-size: 1.125rem; }
            #top-bar > a.text-xl span,
            #top-bar > a.text-xl { white-space: nowrap; }
            #search-input { height: 38px; font-size: 0.875rem; padding: 0 14px; min-width: 0; }
            #top-bar .ml-auto > span,
            #top-bar .ml-auto > a { display: none; }

            /* Sidebar → drawer */
            #sidebar {
                position: fixed; top: 64px; left: 0; bottom: 0;
                width: 280px; z-index: 50;
                background: #fff;
                box-shadow: 4px 0 16px rgba(0,0,0,.15);
                transform: translateX(-100%); transition: transform 0.25s ease;
            }
            #sidebar.open { transform: translateX(0); }

            /* Doc row — compact */
            .doc-row { padding: 0 8px; height: auto; min-height: 60px; flex-wrap: wrap; gap: 4px 6px; padding-top: 6px; padding-bottom: 6px; }
            .doc-row .chk { margin-right: 4px; }
            .doc-row .star-btn { margin-right: 4px; }
            .doc-row .sender-col {
                width: auto; max-width: 50%;
                font-size: 0.75rem; padding-right: 0;
                font-weight: 600; color: #444;
            }
            .doc-row .subject-col {
                flex: 1 1 100%; order: 3;
                font-size: 0.8125rem;
                white-space: normal; overflow: visible;
            }
            .doc-row .date-col {
                width: auto; margin-left: auto;
                font-size: 0.7rem;
            }
            .doc-row .doctype-badge { font-size: 0.625rem; padding: 1px 6px; }
            .doc-row .label-chip { max-width: 60px; }

            /* Toolbar — compact */
            #toolbar { padding: 0 8px; }
            .toolbar-btn { padding: 6px 8px; font-size: 0.75rem; }

            /* Category tabs */
            .cat-tab { padding: 12px 14px; font-size: 0.8125rem; }

            /* Pagination */
            #pagination { padding: 10px 12px; font-size: 0.75rem; flex-wrap: wrap; justify-content: center; }

            /* Modals */
            .modal-box { width: 95vw !important; max-height: 90vh; overflow-y: auto; }
            .modal-header { padding: 12px 14px; font-size: 0.9375rem; }
            .modal-body { padding: 12px 14px; }
            .modal-footer { padding: 10px 14px; flex-wrap: wrap; }
        }

        @media (max-width: 480px) {
            #top-bar > a.text-xl span:not(.material-icons) { display: none; }
            .cat-tab { padding: 10px 12px; }
            .doc-row .sender-col { max-width: 60%; }
        }
    </style>
</head>
<body>

<div id="inbox-shell">

    <!-- Top Bar -->
    <div id="top-bar">
        <div id="hamburger-btn" onclick="toggleSidebar()" title="เมนู">
            <i class="fas fa-bars text-gray-600"></i>
        </div>
        <a href="<?= base_url('index.php/edoc') ?>" class="text-xl font-bold text-indigo-700 flex items-center gap-2" style="white-space:nowrap">
            <i class="fas fa-inbox"></i> <span>E-Document</span>
        </a>
        <input id="search-input" type="text" placeholder="ค้นหาเอกสาร...">
        <div class="ml-auto flex items-center gap-3">
            <?php if ($isEdocAdmin): ?>
            <a href="<?= base_url('index.php/edoc/admin') ?>" class="text-sm text-blue-600 hover:underline">Admin</a>
            <?php endif; ?>
            <span class="text-sm text-gray-600"><?= esc($infoUser['tf_name'] ?? '') ?> <?= esc($infoUser['tl_name'] ?? '') ?></span>
        </div>
    </div>

    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop" onclick="toggleSidebar(false)"></div>

    <!-- Main -->
    <div id="main-area">

        <!-- Sidebar -->
        <div id="sidebar">
            <div class="px-3 pt-2 pb-3">
                <button onclick="showComposeForward()" class="w-full flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-2xl px-5 py-3 transition">
                    <i class="fas fa-paper-plane"></i> ส่งต่อเอกสาร
                </button>
            </div>

            <div class="sidebar-nav-item active" data-tab="inbox" onclick="switchTab('inbox',this)">
                <i class="fas fa-inbox w-5 text-center"></i> กล่องรับเอกสาร
                <span class="count-badge" id="inbox-count"><?= (int) $unreadCount > 0 ? (int) $unreadCount : '' ?></span>
            </div>
            <div class="sidebar-nav-item" data-tab="starred" onclick="switchTab('starred',this)">
                <i class="fas fa-star w-5 text-center text-yellow-400"></i> ติดดาว
            </div>
            <div class="sidebar-nav-item" data-tab="forwarded" onclick="switchTab('forwarded',this)">
                <i class="fas fa-share w-5 text-center text-blue-500"></i> ส่งต่อมาให้ฉัน
            </div>
            <div class="sidebar-nav-item" data-tab="all" onclick="switchTab('all',this)">
                <i class="fas fa-layer-group w-5 text-center text-gray-400"></i> ทั้งหมด
            </div>
            <div class="sidebar-nav-item" data-tab="archived" onclick="switchTab('archived',this)">
                <i class="fas fa-archive w-5 text-center text-gray-400"></i> เก็บถาวร
            </div>

            <!-- Labels -->
            <div class="sidebar-section-title">Labels ของฉัน</div>
            <div id="label-list">
                <?php foreach ($userLabels as $lbl): ?>
                <div class="sidebar-label-item" data-label-id="<?= (int)$lbl['id'] ?>" onclick="switchLabel(<?= (int)$lbl['id'] ?>, this)">
                    <span class="label-dot" style="background:<?= esc($lbl['color']) ?>"></span>
                    <span class="flex-1 truncate"><?= esc($lbl['name']) ?></span>
                    <button class="ml-auto text-gray-400 hover:text-gray-600 text-xs px-1" onclick="event.stopPropagation(); editLabel(<?= (int)$lbl['id'] ?>, '<?= esc($lbl['name']) ?>', '<?= esc($lbl['color']) ?>')" title="แก้ไข"><i class="fas fa-pen"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="sidebar-label-item mt-1" onclick="openCreateLabelModal()">
                <i class="fas fa-plus w-5 text-center text-gray-400"></i>
                <span class="text-gray-500">สร้าง Label ใหม่</span>
            </div>
        </div>

        <!-- Content area -->
        <div id="content-area">

            <!-- Category tabs -->
            <div id="category-tabs">
                <div class="cat-tab active" data-doctype="" onclick="switchDoctype('', this)">
                    <i class="fas fa-th-large"></i> ทั้งหมด
                </div>
                <?php foreach ($doctypes as $dt): ?>
                <div class="cat-tab" data-doctype="<?= esc($dt) ?>" onclick="switchDoctype('<?= esc($dt) ?>', this)">
                    <?= esc($dt) ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Toolbar -->
            <div id="toolbar">
                <div class="toolbar-btn" onclick="archiveSelected()" title="เก็บถาวรที่เลือก">
                    <i class="fas fa-archive"></i> <span class="hidden sm:inline">เก็บถาวร</span>
                </div>
                <div class="text-gray-300 mx-1">|</div>
                <div class="toolbar-btn" onclick="openApplyLabelModal()" title="ติด Label">
                    <i class="fas fa-tag"></i> <span class="hidden sm:inline">ติด Label</span>
                </div>
                <div class="toolbar-btn" onclick="openForwardModal()" title="ส่งต่อ">
                    <i class="fas fa-share"></i> <span class="hidden sm:inline">ส่งต่อ</span>
                </div>
                <div id="toolbar-select-info" class="text-xs text-gray-500 ml-2 hidden"></div>
            </div>

            <!-- Doc list -->
            <div id="doc-list-wrap">
                <div id="loading-wrap"><div class="spinner"></div></div>
                <div id="doc-list"></div>
                <div id="empty-state" style="display:none">
                    <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                    <p class="text-lg">ไม่มีเอกสาร</p>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination" style="display:none">
                <span id="page-info"></span>
                <div class="page-btn" id="btn-prev" onclick="prevPage()"><i class="fas fa-chevron-left"></i></div>
                <div class="page-btn" id="btn-next" onclick="nextPage()"><i class="fas fa-chevron-right"></i></div>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     Forward Modal
     ============================================================ -->
<div class="modal-overlay" id="forward-modal">
    <div class="modal-box">
        <div class="modal-header">
            <span><i class="fas fa-share mr-2 text-blue-500"></i>ส่งต่อเอกสาร</span>
            <button onclick="closeModal('forward-modal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body space-y-3">
            <div id="forward-doc-info" class="text-sm text-gray-700 bg-gray-50 rounded p-3 leading-relaxed"></div>
            <label class="block text-sm font-semibold text-gray-700">ส่งถึง</label>
            <div class="relative">
                <div class="tag-input-wrap" id="recipients-wrap" onclick="document.getElementById('recipient-input').focus()">
                    <input id="recipient-input" class="tag-text-input" placeholder="พิมพ์ชื่อหรืออีเมล..." autocomplete="off">
                </div>
                <div id="suggest-dropdown" class="suggest-dropdown" style="display:none"></div>
            </div>
            <label class="block text-sm font-semibold text-gray-700">หมายเหตุ <span class="font-normal text-gray-400">(ไม่บังคับ)</span></label>
            <textarea id="forward-note" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-['Sarabun'] resize-none focus:outline-none focus:border-blue-400" placeholder="เหตุผลหรือข้อความประกอบ..."></textarea>
            <p class="text-xs text-gray-400"><i class="fas fa-info-circle mr-1"></i>เอกสารต้นฉบับจะถูกแชร์โดยไม่คัดลอกไฟล์</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('forward-modal')" class="px-4 py-2 text-sm rounded border border-gray-300 hover:bg-gray-50">ยกเลิก</button>
            <button onclick="submitForward()" class="px-5 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700 font-semibold">ส่งต่อ</button>
        </div>
    </div>
</div>

<!-- ============================================================
     Create / Edit Label Modal
     ============================================================ -->
<div class="modal-overlay" id="label-modal">
    <div class="modal-box" style="width:360px">
        <div class="modal-header">
            <span id="label-modal-title"><i class="fas fa-tag mr-2 text-gray-500"></i>สร้าง Label</span>
            <button onclick="closeModal('label-modal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body space-y-4">
            <input type="hidden" id="label-edit-id" value="">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ชื่อ Label</label>
                <input id="label-name-input" type="text" maxlength="100" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-400" placeholder="เช่น งบประมาณ, ด่วน, HR">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">สี</label>
                <div class="flex gap-2 flex-wrap" id="color-swatches">
                    <?php
                    $swatchColors = ['#e53e3e','#dd6b20','#d69e2e','#38a169','#3182ce','#805ad5','#d53f8c','#e2e8f0','#4a5568','#6b7280'];
                    foreach ($swatchColors as $c):
                    ?>
                    <div class="color-swatch" style="background:<?= $c ?>" data-color="<?= $c ?>" onclick="selectColor('<?= $c ?>')"></div>
                    <?php endforeach; ?>
                </div>
                <input id="label-color-input" type="color" value="#6b7280" class="mt-2 w-full h-9 rounded border border-gray-200 cursor-pointer">
            </div>
        </div>
        <div class="modal-footer">
            <button id="label-delete-btn" onclick="confirmDeleteLabel()" class="mr-auto px-3 py-2 text-sm rounded text-red-600 hover:bg-red-50 hidden">
                <i class="fas fa-trash mr-1"></i>ลบ
            </button>
            <button onclick="closeModal('label-modal')" class="px-4 py-2 text-sm rounded border border-gray-300 hover:bg-gray-50">ยกเลิก</button>
            <button onclick="saveLabel()" class="px-5 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700 font-semibold">บันทึก</button>
        </div>
    </div>
</div>

<!-- Apply Label to doc modal -->
<div class="modal-overlay" id="apply-label-modal">
    <div class="modal-box" style="width:300px">
        <div class="modal-header">
            <span><i class="fas fa-tag mr-2"></i>ติด Label</span>
            <button onclick="closeModal('apply-label-modal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="apply-label-list"></div>
        <div class="modal-footer">
            <button onclick="closeModal('apply-label-modal')" class="px-4 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">เสร็จ</button>
        </div>
    </div>
</div>

<script>
// ============================================================
// State
// ============================================================
const BASE_URL = '<?= base_url("index.php/") ?>';
let currentTab    = 'inbox';
let currentDoctype = '';
let currentLabelId = 0;
let currentPage   = 1;
let totalDocs     = 0;
const PER_PAGE    = 30;
let searchTimer   = null;
let allDocs       = [];  // current page rows
let selectedIds   = new Set();
let activeForwardDocId = null;
let recipientTags = [];
const COLORS = <?= json_encode($swatchColors ?? ['#e53e3e','#dd6b20','#d69e2e','#38a169','#3182ce','#805ad5','#d53f8c','#e2e8f0','#4a5568','#6b7280']) ?>;
let userLabels = <?= json_encode(array_values($userLabels)) ?>;
let selectedColor = '#6b7280';

// ============================================================
// Load inbox data
// ============================================================
function loadInbox() {
    const list = document.getElementById('doc-list');
    const empty = document.getElementById('empty-state');
    const loading = document.getElementById('loading-wrap');
    const pagination = document.getElementById('pagination');

    list.innerHTML = '';
    empty.style.display = 'none';
    loading.style.display = 'flex';
    pagination.style.display = 'none';

    const search = document.getElementById('search-input').value.trim();
    const params = new URLSearchParams({
        tab:      currentTab,
        doctype:  currentDoctype,
        label_id: currentLabelId,
        page:     currentPage,
        search:   search,
    });

    fetch(`${BASE_URL}edoc/inbox/data?${params}`)
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            if (!data.rows || data.rows.length === 0) {
                empty.style.display = 'flex';
                return;
            }
            allDocs = data.rows;
            totalDocs = data.total;
            renderDocList(data.rows);
            renderPagination(data.total, data.page, data.per_page);
        })
        .catch(() => {
            loading.style.display = 'none';
            empty.style.display = 'flex';
        });
}

function renderDocList(rows) {
    const list = document.getElementById('doc-list');
    list.innerHTML = rows.map(doc => renderDocRow(doc)).join('');
}

function renderDocRow(doc) {
    const isUnread = !doc.is_read;
    const isStarred = doc.is_starred == 1;
    const rowClass = isUnread ? 'unread' : 'read';
    const starClass = isStarred ? 'starred' : '';
    const starIcon  = isStarred ? 'fas fa-star' : 'far fa-star';
    const fwBadge   = doc.forwarded_by ? `<span class="text-xs text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full"><i class="fas fa-share mr-1"></i>${escHtml(senderShort(doc.forwarded_by))}</span>` : '';

    const labelChips = (doc.labels || []).map(l =>
        `<span class="label-chip" style="background:${escHtml(l.color)}">${escHtml(l.name)}</span>`
    ).join('');

    const dateStr = formatDate(doc.datedoc);
    const senderName = doc.owner_display || doc.owner || '';
    const docBadge = doc.doctype ? `<span class="doctype-badge">${escHtml(doc.doctype)}</span>` : '';

    return `<div class="doc-row ${rowClass}" data-id="${doc.iddoc}"
        onclick="openDocument(event, ${doc.iddoc})"
        onmouseenter="this.querySelector('.chk').style.opacity='1'"
        onmouseleave="if(!this.classList.contains('selected')){this.querySelector('.chk').style.opacity='0'}">
        <span class="chk" style="opacity:0">
            <input type="checkbox" class="doc-checkbox" value="${doc.iddoc}"
                onclick="event.stopPropagation(); toggleSelect(${doc.iddoc}, this)"
                ${selectedIds.has(doc.iddoc) ? 'checked' : ''}>
        </span>
        <span class="star-btn ${starClass}" onclick="event.stopPropagation(); toggleStar(${doc.iddoc}, this)">
            <i class="${starIcon}"></i>
        </span>
        <span class="sender-col font-${isUnread ? '700' : '400'}">${escHtml(senderShort(senderName))}${fwBadge}</span>
        <span class="subject-col">
            ${docBadge}
            ${labelChips}
            <span class="doc-title-text text-sm truncate">${escHtml(doc.officeiddoc ? doc.officeiddoc + ' — ' : '')}${escHtml(doc.title)}</span>
        </span>
        <span class="date-col">${dateStr}</span>
    </div>`;
}

function renderPagination(total, page, perPage) {
    const pagination = document.getElementById('pagination');
    if (total <= perPage) { pagination.style.display = 'none'; return; }
    pagination.style.display = 'flex';
    const from = (page - 1) * perPage + 1;
    const to   = Math.min(page * perPage, total);
    document.getElementById('page-info').textContent = `${from}–${to} จาก ${total}`;
    document.getElementById('btn-prev').classList.toggle('disabled', page <= 1);
    document.getElementById('btn-next').classList.toggle('disabled', to >= total);
}

// ============================================================
// Navigation
// ============================================================
function toggleSidebar(force) {
    const sb = document.getElementById('sidebar');
    const bd = document.getElementById('sidebar-backdrop');
    const open = (typeof force === 'boolean') ? force : !sb.classList.contains('open');
    sb.classList.toggle('open', open);
    bd.classList.toggle('open', open);
}

function switchTab(tab, el) {
    currentTab = tab;
    currentDoctype = '';
    currentLabelId = 0;
    currentPage = 1;
    document.querySelectorAll('.sidebar-nav-item').forEach(e => e.classList.remove('active'));
    document.querySelectorAll('.sidebar-label-item').forEach(e => e.classList.remove('active'));
    if (el) el.classList.add('active');
    resetDoctypeTab();
    loadInbox();
    if (window.matchMedia('(max-width:768px)').matches) toggleSidebar(false);
}

function switchDoctype(dt, el) {
    currentDoctype = dt;
    currentPage = 1;
    document.querySelectorAll('.cat-tab').forEach(e => e.classList.remove('active'));
    if (el) el.classList.add('active');
    loadInbox();
}

function switchLabel(labelId, el) {
    currentTab = 'all';
    currentLabelId = labelId;
    currentDoctype = '';
    currentPage = 1;
    document.querySelectorAll('.sidebar-nav-item').forEach(e => e.classList.remove('active'));
    document.querySelectorAll('.sidebar-label-item').forEach(e => e.classList.remove('active'));
    if (el) el.classList.add('active');
    resetDoctypeTab();
    loadInbox();
    if (window.matchMedia('(max-width:768px)').matches) toggleSidebar(false);
}

function resetDoctypeTab() {
    document.querySelectorAll('.cat-tab').forEach(e => e.classList.remove('active'));
    document.querySelector('.cat-tab[data-doctype=""]').classList.add('active');
}

function prevPage() { if (currentPage > 1) { currentPage--; loadInbox(); } }
function nextPage() {
    if (currentPage * PER_PAGE < totalDocs) { currentPage++; loadInbox(); }
}

// ============================================================
// Open doc + mark read
// ============================================================
function openDocument(evt, docId) {
    if (evt.target.closest('input[type=checkbox]') || evt.target.closest('.star-btn')) return;
    // Mark read
    fetch(`${BASE_URL}edoc/inbox/mark-read`, {
        method: 'POST', headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({doc_id: docId})
    });
    // Update row to read
    const row = document.querySelector(`.doc-row[data-id="${docId}"]`);
    if (row) { row.classList.remove('unread'); row.classList.add('read'); }
    // Open PDF viewer
    window.open(`${BASE_URL}edoc/viewPDF/${docId}`, '_blank');
}

// ============================================================
// Star
// ============================================================
function toggleStar(docId, el) {
    fetch(`${BASE_URL}edoc/inbox/star`, {
        method: 'POST', headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({doc_id: docId})
    }).then(r => r.json()).then(data => {
        const isStarred = data.is_starred == 1;
        el.classList.toggle('starred', isStarred);
        el.innerHTML = `<i class="${isStarred ? 'fas' : 'far'} fa-star"></i>`;
    });
}

// ============================================================
// Select
// ============================================================
function toggleSelect(docId, cb) {
    const row = document.querySelector(`.doc-row[data-id="${docId}"]`);
    if (cb.checked) {
        selectedIds.add(docId);
        row && row.classList.add('selected');
    } else {
        selectedIds.delete(docId);
        row && row.classList.remove('selected');
    }
    updateToolbarInfo();
}

function updateToolbarInfo() {
    const info = document.getElementById('toolbar-select-info');
    if (selectedIds.size > 0) {
        info.textContent = `เลือก ${selectedIds.size} รายการ`;
        info.classList.remove('hidden');
    } else {
        info.classList.add('hidden');
    }
}

// ============================================================
// Archive
// ============================================================
function archiveSelected() {
    if (selectedIds.size === 0) { alert('เลือกเอกสารก่อน'); return; }
    const ids = [...selectedIds];
    Promise.all(ids.map(id => fetch(`${BASE_URL}edoc/inbox/archive`, {
        method: 'POST', headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({doc_id: id, archived: true})
    }))).then(() => {
        selectedIds.clear();
        loadInbox();
    });
}

// ============================================================
// Label CRUD
// ============================================================
function openCreateLabelModal() {
    document.getElementById('label-edit-id').value = '';
    document.getElementById('label-name-input').value = '';
    document.getElementById('label-color-input').value = '#6b7280';
    document.getElementById('label-modal-title').innerHTML = '<i class="fas fa-tag mr-2 text-gray-500"></i>สร้าง Label';
    document.getElementById('label-delete-btn').classList.add('hidden');
    selectedColor = '#6b7280';
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    openModal('label-modal');
}

function editLabel(id, name, color) {
    document.getElementById('label-edit-id').value = id;
    document.getElementById('label-name-input').value = name;
    document.getElementById('label-color-input').value = color;
    document.getElementById('label-modal-title').innerHTML = '<i class="fas fa-tag mr-2 text-gray-500"></i>แก้ไข Label';
    document.getElementById('label-delete-btn').classList.remove('hidden');
    selectedColor = color;
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.classList.toggle('selected', s.dataset.color === color);
    });
    openModal('label-modal');
}

function selectColor(color) {
    selectedColor = color;
    document.getElementById('label-color-input').value = color;
    document.querySelectorAll('.color-swatch').forEach(s =>
        s.classList.toggle('selected', s.dataset.color === color));
}

function saveLabel() {
    const id    = document.getElementById('label-edit-id').value;
    const name  = document.getElementById('label-name-input').value.trim();
    const color = document.getElementById('label-color-input').value || selectedColor;
    if (!name) { alert('กรุณาระบุชื่อ label'); return; }

    const url    = id ? `${BASE_URL}edoc/labels/${id}` : `${BASE_URL}edoc/labels`;
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method, headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({name, color})
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') {
            closeModal('label-modal');
            refreshLabels();
        } else {
            alert(data.error || 'เกิดข้อผิดพลาด');
        }
    });
}

function confirmDeleteLabel() {
    const id = document.getElementById('label-edit-id').value;
    if (!id || !confirm('ลบ label นี้? เอกสารที่ติด label นี้จะไม่ถูกลบ')) return;
    fetch(`${BASE_URL}edoc/labels/${id}`, {
        method: 'DELETE', headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(() => { closeModal('label-modal'); refreshLabels(); });
}

function refreshLabels() {
    fetch(`${BASE_URL}edoc/labels`)
        .then(r => r.json())
        .then(data => {
            userLabels = data.labels || [];
            renderSidebarLabels();
        });
}

function renderSidebarLabels() {
    const container = document.getElementById('label-list');
    container.innerHTML = userLabels.map(lbl => `
        <div class="sidebar-label-item" data-label-id="${lbl.id}" onclick="switchLabel(${lbl.id}, this)">
            <span class="label-dot" style="background:${escHtml(lbl.color)}"></span>
            <span class="flex-1 truncate">${escHtml(lbl.name)}</span>
            <button class="ml-auto text-gray-400 hover:text-gray-600 text-xs px-1"
                onclick="event.stopPropagation(); editLabel(${lbl.id}, '${escHtml(lbl.name)}', '${escHtml(lbl.color)}')"
                title="แก้ไข"><i class="fas fa-pen"></i></button>
        </div>`).join('');
}

// Apply label to selected docs
function openApplyLabelModal() {
    if (selectedIds.size === 0) { alert('เลือกเอกสารก่อน'); return; }
    const container = document.getElementById('apply-label-list');
    if (userLabels.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500 p-2">ยังไม่มี label <button class="text-blue-600 underline" onclick="closeModal(\'apply-label-modal\'); openCreateLabelModal()">สร้างใหม่</button></p>';
    } else {
        container.innerHTML = userLabels.map(lbl => `
            <div class="flex items-center gap-3 py-2 px-1 hover:bg-gray-50 rounded cursor-pointer"
                onclick="applyLabelToSelected(${lbl.id})">
                <span class="label-dot" style="background:${escHtml(lbl.color)}"></span>
                <span class="text-sm">${escHtml(lbl.name)}</span>
            </div>`).join('');
    }
    openModal('apply-label-modal');
}

function applyLabelToSelected(labelId) {
    const ids = [...selectedIds];
    Promise.all(ids.map(id => fetch(`${BASE_URL}edoc/documents/${id}/labels`, {
        method: 'POST', headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({label_id: labelId, remove: false})
    }))).then(() => { closeModal('apply-label-modal'); loadInbox(); });
}

// ============================================================
// Forward
// ============================================================
function showComposeForward() {
    if (selectedIds.size === 1) {
        activeForwardDocId = [...selectedIds][0];
        const doc = allDocs.find(d => d.iddoc == activeForwardDocId);
        document.getElementById('forward-doc-info').innerHTML = doc
            ? `<strong>${escHtml(doc.officeiddoc || '')}</strong> ${escHtml(doc.title)}`
            : '';
    } else if (selectedIds.size > 1) {
        alert('เลือกเพียงเอกสารเดียวสำหรับการส่งต่อ');
        return;
    } else {
        activeForwardDocId = null;
        document.getElementById('forward-doc-info').innerHTML = '<span class="text-red-500">กรุณาเลือกเอกสาร 1 รายการก่อน</span>';
    }
    recipientTags = [];
    renderRecipientTags();
    document.getElementById('forward-note').value = '';
    openModal('forward-modal');
}

function openForwardModal() { showComposeForward(); }

function submitForward() {
    if (!activeForwardDocId) { alert('กรุณาเลือกเอกสารก่อน'); closeModal('forward-modal'); return; }
    if (recipientTags.length === 0) { alert('กรุณาระบุผู้รับ'); return; }
    const note = document.getElementById('forward-note').value.trim();

    fetch(`${BASE_URL}edoc/documents/${activeForwardDocId}/forward`, {
        method: 'POST', headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({ to_emails: recipientTags.map(t => t.email), note })
    }).then(r => r.json()).then(data => {
        if (data.status === 'success' || data.status === 'partial') {
            closeModal('forward-modal');
            const msg = data.forwarded.length > 0
                ? `ส่งต่อให้ ${data.forwarded.join(', ')} สำเร็จ`
                : 'ส่งต่อเรียบร้อย';
            showToast(msg);
        } else {
            alert(data.error || 'เกิดข้อผิดพลาด');
        }
    });
}

// Recipient tag input
const recipientInput = document.getElementById('recipient-input');
const suggestDrop = document.getElementById('suggest-dropdown');
let suggestTimer = null;

recipientInput.addEventListener('input', () => {
    clearTimeout(suggestTimer);
    const q = recipientInput.value.trim();
    if (q.length < 1) { suggestDrop.style.display = 'none'; return; }
    suggestTimer = setTimeout(() => fetchSuggestions(q), 250);
});

recipientInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const v = recipientInput.value.trim().replace(/,$/, '');
        if (v && v.includes('@')) addRecipientTag(v, v);
    } else if (e.key === 'Backspace' && recipientInput.value === '' && recipientTags.length > 0) {
        recipientTags.pop();
        renderRecipientTags();
    }
});

recipientInput.addEventListener('blur', () => setTimeout(() => suggestDrop.style.display = 'none', 200));

function fetchSuggestions(q) {
    fetch(`${BASE_URL}edoc/inbox/suggest-emails?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
            const results = data.results || [];
            if (results.length === 0) { suggestDrop.style.display = 'none'; return; }
            suggestDrop.innerHTML = results.map(r =>
                `<div class="suggest-item" onmousedown="addRecipientTag('${escHtml(r.email)}', '${escHtml(r.name || r.email)}')">
                    <span class="font-semibold">${escHtml(r.name || r.email)}</span>
                    <span class="text-xs text-gray-400 ml-2">${escHtml(r.email)}</span>
                </div>`).join('');
            suggestDrop.style.display = 'block';
        });
}

function addRecipientTag(email, name) {
    email = email.toLowerCase().trim();
    if (!email || recipientTags.find(t => t.email === email)) return;
    recipientTags.push({email, name: name || email});
    recipientInput.value = '';
    suggestDrop.style.display = 'none';
    renderRecipientTags();
}

function removeRecipientTag(email) {
    recipientTags = recipientTags.filter(t => t.email !== email);
    renderRecipientTags();
}

function renderRecipientTags() {
    const wrap = document.getElementById('recipients-wrap');
    const existing = wrap.querySelectorAll('.tag-item');
    existing.forEach(e => e.remove());
    const fragment = document.createDocumentFragment();
    recipientTags.forEach(t => {
        const span = document.createElement('span');
        span.className = 'tag-item';
        span.innerHTML = `${escHtml(t.name)} <button type="button" onclick="removeRecipientTag('${escHtml(t.email)}')">&times;</button>`;
        fragment.appendChild(span);
    });
    wrap.insertBefore(fragment, recipientInput);
}

// ============================================================
// Modal helpers
// ============================================================
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

// ============================================================
// Toast
// ============================================================
function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-xl text-sm z-50 transition';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

// ============================================================
// Search
// ============================================================
document.getElementById('search-input').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentPage = 1; loadInbox(); }, 400);
});

// ============================================================
// Utility
// ============================================================
function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
function senderShort(name) {
    if (!name) return '';
    if (name.includes('@')) {
        return name.split('@')[0];
    }
    const parts = name.trim().split(' ');
    return parts.length >= 2 ? parts[0] + ' ' + parts[1] : name;
}
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const now = new Date();
    const diff = now - d;
    if (diff < 86400000 && d.getDate() === now.getDate()) {
        return d.toLocaleTimeString('th-TH', {hour:'2-digit', minute:'2-digit'});
    }
    const yesterday = new Date(now); yesterday.setDate(now.getDate()-1);
    if (d.getDate() === yesterday.getDate() && d.getMonth() === yesterday.getMonth()) return 'เมื่อวาน';
    if (d.getFullYear() === now.getFullYear()) {
        return d.toLocaleDateString('th-TH', {day:'numeric', month:'short'});
    }
    return d.toLocaleDateString('th-TH', {day:'numeric', month:'short', year:'2-digit'});
}

// color input sync
document.getElementById('label-color-input').addEventListener('input', function() {
    selectedColor = this.value;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
});

// ============================================================
// Initial load
// ============================================================
loadInbox();
</script>
</body>
</html>
