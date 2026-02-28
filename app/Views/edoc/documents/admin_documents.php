<!DOCTYPE html>
<html lang="th" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Document Manager | จัดการเอกสาร</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {}
            },
            corePlugins: {
                preflight: false
            }
        }
    </script>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core CSS (modal สำหรับ form / preview) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/backend.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= base_url('assets/css/backend-plugin.min.css') ?>">

    <!-- Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/remixicon/fonts/remixicon.css') ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Plugin CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/amsify.suggestags.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" />
    <!-- DataTables (ธีมเดียวกับหน้า) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    <style>
        body {
            box-sizing: border-box;
        }

        * {
            font-family: 'Prompt', sans-serif;
        }

        .modal-overlay {
            animation: fadeIn 0.2s ease-out;
        }

        .modal-content {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .loading-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }

        .doc-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .doc-card:hover {
            transform: translateY(-4px);
        }

        .preview-container {
            background: linear-gradient(180deg, #f8f9fb 0%, #f3f4f8 100%);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 3px;
        }

        #modalCenter.modal {
            z-index: 1055;
        }

        #modalCenter .modal-dialog {
            z-index: 1056;
        }

        body.modal-open .modal-backdrop {
            display: none !important;
        }

        body.modal-open .modal .modal-dialog .modal-content {
            animation: slideUp 0.3s ease-out;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .view_model {
            z-index: 9999;
        }

        .doc-view-modal .pdf-container {
            min-height: 70vh;
            background: #f8fafc;
            border-radius: 0.5rem;
        }

        .custom-file {
            position: relative;
            display: flex;
            width: 100%;
        }

        .custom-file-input {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 2.5rem;
            margin: 0;
            opacity: 0;
        }

        .custom-file-label {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1;
            padding: 0.5rem 0.75rem;
            line-height: 1.5;
            color: #374151;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doc-action-buttons {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem;
        }

        .doc-action-buttons .doc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            padding: 0;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .doc-action-buttons .doc-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .doc-action-buttons .doc-btn i {
            font-size: 1rem;
        }

        .doc-action-buttons .doc-btn-view {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #fff !important;
        }

        .doc-action-buttons .doc-btn-view:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            color: #fff !important;
        }

        .doc-action-buttons .doc-btn-edit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff !important;
        }

        .doc-action-buttons .doc-btn-edit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #fff !important;
        }

        .doc-action-buttons .doc-btn-stats {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #fff !important;
        }

        .doc-action-buttons .doc-btn-stats:hover {
            background: linear-gradient(135deg, #b45309 0%, #92400e 100%);
            color: #fff !important;
        }

        #imageModal .modal-body {
            background: #000;
        }

        #imageModal img {
            max-height: 80vh;
            object-fit: contain;
        }

        .alert {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .alert-info {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .list-group-item {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        /* ประเภทเอกสาร dropdown ให้เข้ากับฟอร์ม */
        #formModal .bootstrap-select .dropdown-toggle,
        #formModal select#doctype {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #374151;
            background: #fff;
            min-height: 2.5rem;
            outline: none;
            box-shadow: none;
        }

        #formModal .bootstrap-select .dropdown-toggle:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        #formModal .bootstrap-select .dropdown-toggle:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        #formModal .bootstrap-select.btn-group {
            width: 100% !important;
        }

        #formModal .bootstrap-select .dropdown-toggle .filter-option {
            text-align: right;
        }

        /* หน้าเต็มไม่มี sidebar */
        body.doc-full-page .content-page {
            margin-left: 0;
            width: 100%;
            padding: 0;
        }

        /* DataTables ธีมเดียวกับหน้า + ความกว้างคอลัมน์ */
        .dataTables_wrapper {
            font-size: 0.875rem;
            width: 100%;
        }

        #documents-table {
            width: 100% !important;
        }

        #documents-table thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: #475569;
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        #documents-table thead th.sorting:hover,
        #documents-table thead th.sorting_asc,
        #documents-table thead th.sorting_desc {
            color: #1e40af;
        }

        #documents-table tbody td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        #documents-table tbody tr:hover {
            background: #f8fafc;
        }

        #documents-table tbody tr:hover td {
            background: transparent;
        }

        /* ชื่อเรื่อง - ตัดทิ้งถ้าเกิน */
        #documents-table thead th:nth-child(3),
        #documents-table tbody td:nth-child(3) {
            max-width: 16em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ผู้รับ - ตัดทิ้งถ้าเกิน */
        #documents-table thead th:nth-child(6),
        #documents-table tbody td:nth-child(6) {
            max-width: 12em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* วันที่ - ความกว้างพอดี */
        #documents-table thead th:nth-child(7),
        #documents-table tbody td:nth-child(7) {
            min-width: 7rem;
            width: 7rem;
        }

        /* จัดการ - แถวเดียว แคบ */
        #documents-table thead th:nth-child(10),
        #documents-table tbody td:nth-child(10) {
            width: 1%;
            white-space: nowrap;
        }

        .doc-action-buttons {
            flex-wrap: nowrap;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 0.5rem;
            color: #64748b;
            font-size: 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem;
            margin: 0 1px;
            border-radius: 0.375rem;
            font-size: 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3b82f6 !important;
            color: #fff !important;
            border: none !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
            border: none !important;
        }

        .dataTables_wrapper .dataTables_length label {
            font-size: 0.875rem;
            color: #64748b;
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body class="color-light min-h-full doc-full-page">
    <div class="wrapper min-h-full">
        <div class="content-page min-h-full overflow-auto" style="background: linear-gradient(135deg, #f8f9fb 0%, #f3f4f8 100%); margin-left: 0;">
            <div id="app" class="h-full overflow-auto">
                <!-- Header (เหมือน reference 100%) -->
                <header class="sticky top-0 z-40 backdrop-blur-xl bg-white/80 border-b border-gray-200/50">
                    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow shadow-blue-400/20">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h1 id="app-title" class="text-base font-bold text-gray-900">Document Manager</h1>
                                    <p id="doc-count" class="text-xs text-gray-500">0 เอกสาร</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1 sm:w-48">
                                    <input type="text" id="search-input" placeholder="ค้นหา..." class="w-full pl-8 pr-3 py-1.5 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400/20 transition-all">
                                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <button type="button" id="add-btn" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg shadow shadow-blue-500/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span class="hidden sm:inline">เพิ่มเอกสาร</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main: DataTables ขยายตามขนาดหน้าจอ -->
                <main class="w-full px-3 sm:px-4 py-3">
                    <div id="doc-table-card" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden px-3 py-2 hidden">
                        <table id="documents-table" class="display stripe hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID เอกสาร</th>
                                    <th>เลขที่หนังสือ</th>
                                    <th>ชื่อเรื่อง</th>
                                    <th>ประเภท</th>
                                    <th>ผู้จัดทำ</th>
                                    <th>ผู้รับ</th>
                                    <th>วันที่</th>
                                    <th>หน้า</th>
                                    <th>จำนวนดู</th>
                                    <th class="no-sort">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div id="empty-state" class="hidden flex-col items-center justify-center py-12">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 id="empty-title" class="text-base font-semibold text-gray-900 mb-1">ยังไม่มีเอกสาร</h3>
                        <p class="text-gray-600 text-center text-sm mb-4">เริ่มต้นเพิ่มเอกสารแรกของคุณ</p>
                        <button type="button" id="empty-add-btn" class="flex items-center gap-1.5 px-4 py-2 text-sm bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg shadow shadow-blue-500/20 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            เพิ่มเอกสาร
                        </button>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Form Modal: เพิ่ม/แก้ไขเอกสาร -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content rounded-xl overflow-hidden">
                <div class="modal-header border-b border-gray-200 bg-gray-50">
                    <h5 class="modal-title text-lg font-semibold text-gray-800" id="formModalTitle"><i class="fas fa-file-alt me-2"></i>เพิ่มเอกสาร</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">&times;</button>
                </div>
                <div class="modal-body p-6">
                    <form id="frmdata" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">เลขที่หนังสือ</label>
                                <input type="text" id="officeiddoc" name="officeiddoc" required class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <input type="hidden" id="iddoc" name="iddoc">
                                <input type="hidden" id="userid" name="userid" value="<?= $infoUser['uid'] ?>">
                            </div>
                            <div class="md:col-span-7">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเรื่อง</label>
                                <input type="text" id="title" name="title" required class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนหน้า</label>
                                <input type="number" id="pages" name="pages" required class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทเอกสาร</label>
                                <select name="doctype" id="doctype" class="selectpicker form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" data-style="py-0" required>
                                    <option value="">กรุณาระบุ</option>
                                    <option value="หนังสือรับภายใน">หนังสือรับภายใน</option>
                                    <option value="หนังสือรับภายนอก">หนังสือรับภายนอก</option>
                                    <option value="หนังสือส่งภายใน">หนังสือส่งภายใน</option>
                                    <option value="ใบลา">ใบลา</option>
                                    <option value="คำสั่ง">คำสั่ง</option>
                                    <option value="ประกาศ">ประกาศ</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">เล่มเอกสาร (Volume)</label>
                                <select name="volume_id" id="volume_id" class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option value="">-- ไม่ระบุ --</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">วันที่ลงหนังสือ</label>
                                <input type="text" data-date-format="yyyy-mm-dd" name="datedoc" id="datedoc" required class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">เจ้าของเอกสาร</label>
                                <input type="text" name="owner" id="owner" required class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">คำสั่งการ</label>
                            <input type="text" name="order" id="order" class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">กลุ่มผู้รับ (Tag Groups)</label>
                                <div class="flex gap-2 mb-2">
                                    <select id="tagGroupSelect" class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="">-- เลือกกลุ่ม --</option>
                                    </select>
                                    <button type="button" id="btnSaveGroup" class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm whitespace-nowrap"><i class="fas fa-save me-1"></i>บันทึกกลุ่ม</button>
                                    <button type="button" id="btnDeleteGroup" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm whitespace-nowrap" style="display:none;"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="md:col-span-10">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ผู้มีส่วนร่วม</label>
                                <input type="text" name="participant" id="participant" required class="form-control w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <input type="hidden" name="copynum" id="copynum">
                                <input type="hidden" name="allname" id="allname" value="<?= "'" . implode("','", $suggestname) . "'" ?>">
                            </div>
                            <div class="md:col-span-2 flex gap-2">
                                <button type="button" onclick="addtag(1)" class="flex-1 px-3 py-2 border border-blue-500 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 focus:outline-none" title="ทุกคน"><i class="fas fa-users"></i></button>
                                <button type="button" onclick="addtag(0)" class="flex-1 px-3 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none" title="ล้าง"><i class="fas fa-user-minus"></i></button>
                            </div>
                        </div>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:bg-gray-50/50 transition">
                            <div class="flex flex-wrap gap-2 items-center mb-3">
                                <div class="custom-file flex-1 min-w-0">
                                    <input type="file" class="custom-file-input" id="fileupload" name="fileupload" multiple accept=".docx,.pdf,.png,.jpg,.jpeg">
                                    <label class="custom-file-label" for="fileupload">เลือกไฟล์ (หลายไฟล์ได้)</label>
                                </div>
                                <span id="upload_doc" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg cursor-pointer hover:bg-gray-200">อัปโหลด</span>
                            </div>
                            <div id="file-list" class="mb-3" style="display: none;">
                                <p class="text-sm font-medium text-gray-700 mb-2">ไฟล์ที่เลือก</p>
                                <div id="selected-files"></div>
                            </div>
                            <div id="upload-progress" class="mb-3" style="display: none;">
                                <div class="h-2 bg-gray-200 rounded overflow-hidden">
                                    <div class="progress-bar h-full bg-blue-500 rounded transition-all" role="progressbar" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">กำลังอัปโหลด...</p>
                            </div>
                            <div id="uploaded-files" class="mb-3" style="display: none;">
                                <p class="text-sm font-medium text-gray-700 mb-2">ไฟล์ที่อัปโหลดแล้ว</p>
                                <div id="uploaded-files-list"></div>
                            </div>
                            <div id="msgfile" class="text-sm py-2 px-3 rounded-lg hidden"></div>
                            <p class="text-xs text-gray-500">รองรับ: .docx, .pdf, .png, .jpg, .jpeg</p>
                            <input type="hidden" id="fileaddress" name="fileaddress" value="[]">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-t border-gray-200 flex justify-end gap-3 pt-4">
                    <button type="button" class="flex-1 sm:flex-none px-4 py-3 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-900 font-medium rounded-xl transition-all" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="saveForm" value="save" class="flex-1 sm:flex-none px-5 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-xl shadow-lg shadow-blue-500/30 transition-all">เพิ่มข้อมูล</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade view_model" id="statsModal" tabindex="-1" aria-labelledby="statsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-xl overflow-hidden">
                <div class="modal-header border-b border-gray-200 bg-gray-50">
                    <h5 class="modal-title text-lg font-semibold text-gray-800" id="statsModalTitle">ประวัติการเข้าชม</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body p-6" id="statsModalBody"></div>
                <div class="modal-footer border-t border-gray-200">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Details Modal -->
    <div class="modal fade" id="modalCenter" tabindex="-1" aria-labelledby="modalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content doc-view-modal rounded-xl overflow-hidden">
                <div class="modal-header border-b border-gray-200 bg-gray-50">
                    <h5 class="modal-title text-lg font-semibold text-gray-800" id="modalCenterTitle"><i class="bx bx-file-blank me-2"></i>รายละเอียดเอกสาร</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">&times;</button>
                </div>
                <div class="modal-body p-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                        <div class="md:col-span-4">
                            <p class="text-xs font-medium text-gray-500 uppercase">เลขที่หนังสือ</p>
                            <p id="msg_officeiddoc" class="text-gray-800 mt-0.5"></p>
                        </div>
                        <div class="md:col-span-4">
                            <p class="text-xs font-medium text-gray-500 uppercase">วันที่ลงหนังสือ</p>
                            <p id="msg_docdate" class="text-gray-800 mt-0.5"></p>
                        </div>
                        <div class="md:col-span-4">
                            <p class="text-xs font-medium text-gray-500 uppercase">ชนิดเอกสาร</p>
                            <p id="msg_doctype" class="text-gray-800 mt-0.5"></p>
                        </div>
                        <div class="md:col-span-4">
                            <p class="text-xs font-medium text-gray-500 uppercase">เจ้าของเอกสาร</p>
                            <p id="msg_owner" class="text-gray-800 mt-0.5"></p>
                        </div>
                        <div class="md:col-span-8">
                            <p class="text-xs font-medium text-gray-500 uppercase">ชื่อเรื่อง</p>
                            <p id="msg_title" class="text-gray-800 mt-0.5"></p>
                        </div>
                        <div class="md:col-span-12">
                            <p class="text-xs font-medium text-gray-500 uppercase">ผู้เกี่ยวข้อง</p>
                            <p id="msg_participant" class="text-gray-800 mt-0.5"></p>
                        </div>
                        <div class="md:col-span-12">
                            <p class="text-xs font-medium text-gray-500 uppercase">คำสั่งการ</p>
                            <p id="msg_order" class="text-gray-800 mt-0.5"></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4 pt-4 border-t border-gray-200">
                        <h6 class="mb-0 text-sm font-semibold text-gray-700"><i class="bx bx-file-blank me-1"></i>เอกสารแนบ</h6>
                        <a id="msg_fileaddresslink" href="" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">ดาวน์โหลด</a>
                    </div>
                    <div class="pdf-container border border-gray-200 rounded-lg p-2" aria-label="พื้นที่แสดงเอกสาร PDF">
                        <embed id="msg_fileaddress" src="" width="100%" height="600" type="application/pdf">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[1060] flex flex-col gap-2"></div>

    <!-- Footer (มินิมอล) -->
    <footer class="border-t border-gray-200/50 bg-white/50 py-2 mt-4">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 flex flex-col sm:flex-row items-center justify-between gap-1 text-xs text-gray-500">
            <div class="flex gap-4">
                <a href="#" class="hover:text-gray-700">Privacy Policy</a>
                <a href="#" class="hover:text-gray-700">Terms of Use</a>
            </div>
            <div>© <script>
                    document.write(new Date().getFullYear());
                </script> EdocDocument</div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/backend-bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/table-treeview.js') ?>"></script>
    <script src="<?= base_url('assets/js/customizer.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/jquery.amsify.suggestags.js') ?>"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.swalAlert = function(msg, type) {
            type = type || 'info';
            var icon = { success: 'success', error: 'error', warning: 'warning', info: 'info' }[type] || 'info';
            return (typeof Swal !== 'undefined') ? Swal.fire({ icon: icon, title: type === 'error' ? 'เกิดข้อผิดพลาด' : (type === 'success' ? 'สำเร็จ' : ''), text: msg }) : Promise.resolve(alert(msg));
        };
        window.swalConfirm = function(opts) {
            var title = (typeof opts === 'string') ? opts : (opts.title || 'ยืนยัน');
            var text = (typeof opts === 'object' && opts.text) ? opts.text : '';
            var confirmText = (typeof opts === 'object' && opts.confirmText) ? opts.confirmText : 'ตกลง';
            var cancelText = (typeof opts === 'object' && opts.cancelText) ? opts.cancelText : 'ยกเลิก';
            if (typeof Swal === 'undefined') return Promise.resolve(confirm(title + (text ? '\n' + text : '')));
            return Swal.fire({ title: title, text: text, icon: 'question', showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: cancelText, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d' }).then(function(r) { return r.isConfirmed; });
        };
    </script>
    <script>
        let uploadedFiles = [];

        function displaySelectedFiles(files) {
            let filesHtml = '';

            files.forEach((file, index) => {
                const fileSize = formatFileSize(file.size);
                const fileIcon = getFileIcon(file.name);

                filesHtml += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas ${fileIcon} me-2"></i>
                    <div>
                        <div class="fw-medium">${file.name}</div>
                        <small class="text-muted">${fileSize}</small>
                    </div>
                </div>
                <span class="badge bg-secondary">รอการอัปโหลด</span>
            </div>
        `;
            });

            $('#selected-files').html(filesHtml);
            $('#file-list').show();
        }

        function displayUploadedFiles() {
            if (uploadedFiles.length === 0) {
                $('#uploaded-files').hide();
                return;
            }

            let filesHtml = '';

            uploadedFiles.forEach((file, index) => {
                const fileIcon = getFileIcon(file.original_name);

                // Fix: Point to root EdocDocument (removing public/ if present) for preview
                const fileUrl = "<?php echo str_replace('public/', '', base_url('index.php/edoc/viewPDF/')); ?>" + file.filename;

                filesHtml += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas ${fileIcon} me-2"></i>
                    <div>
                        <div class="fw-medium">${file.original_name}</div>
                        <small class="text-muted">
                            <a href="${fileUrl}" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt"></i> ดูไฟล์
                            </a>
                        </small>
                    </div>
                </div>
                <div>
                    <span class="badge bg-success me-2">อัปโหลดสำเร็จ</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUploadedFile(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
            });

            $('#uploaded-files-list').html(filesHtml);
            $('#uploaded-files').show();
        }

        function removeUploadedFile(index) {
            uploadedFiles.splice(index, 1);
            updateFileAddressField();
            displayUploadedFiles();

            if (uploadedFiles.length === 0) {
                $('#msgfile').hide();
            }
        }

        function updateFileAddressField() {
            const fileList = uploadedFiles.map(file => file.filename);
            $('#fileaddress').val(JSON.stringify(fileList));
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            switch (ext) {
                case 'pdf':
                    return 'fa-file-pdf text-danger';
                case 'doc':
                case 'docx':
                    return 'fa-file-word text-primary';
                case 'jpg':
                case 'jpeg':
                case 'png':
                    return 'fa-file-image text-success';
                default:
                    return 'fa-file text-secondary';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        $('.custom-file-input').on('change', function() {
            const files = Array.from(this.files);

            if (files.length === 0) {
                $('#file-list').hide();
                return;
            }

            // Update label
            const fileNames = files.map(file => file.name).join(', ');
            const displayText = files.length > 3 ?
                `${files.length} ไฟล์ที่เลือก` :
                fileNames.length > 50 ? `${files.length} ไฟล์ที่เลือก` : fileNames;

            $(this).siblings('.custom-file-label').addClass('selected').html(displayText);

            // Display selected files
            displaySelectedFiles(files);
        });

        // --- DataTables (ธีมเดียวกับหน้า, Sort ได้) ---
        var getdocUrl = "<?= base_url('index.php/edoc/admin/getdoc') ?>";
        var documentsTable = null;

        function initDocumentsTable() {
            if (documentsTable) return;
            documentsTable = $('#documents-table').DataTable({
                serverSide: true,
                ajax: {
                    url: getdocUrl,
                    type: 'POST',
                    data: function(d) {
                        if (!d.order || d.order.length === 0) {
                            d.order = [{
                                column: 0,
                                dir: 'desc'
                            }];
                        } else {
                            d.order = d.order.filter(function(o) {
                                return o.column !== 9;
                            });
                        }
                        d.columnSearch = [];
                    }
                },
                columns: [{
                        data: 'iddoc'
                    },
                    {
                        data: 'officeiddoc'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'doctype'
                    },
                    {
                        data: 'owner'
                    },
                    {
                        data: 'participant'
                    },
                    {
                        data: 'datedoc'
                    },
                    {
                        data: 'pages'
                    },
                    {
                        data: 'view_count'
                    },
                    {
                        data: 'edit',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: 'lrtip',
                language: {
                    emptyTable: 'ไม่มีข้อมูล',
                    info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
                    infoEmpty: 'แสดง 0 ถึง 0 จาก 0 รายการ',
                    infoFiltered: '(กรองจาก _MAX_ รายการ)',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    loadingRecords: 'กำลังโหลด...',
                    processing: 'กำลังประมวลผล...',
                    search: 'ค้นหา:',
                    zeroRecords: 'ไม่พบข้อมูล'
                },
                drawCallback: function() {
                    var info = this.api().page.info();
                    $('#doc-count').text(info.recordsFiltered + ' เอกสาร');
                    if (info.recordsFiltered === 0) {
                        $('#doc-table-card').addClass('hidden');
                        $('#empty-state').removeClass('hidden').addClass('flex');
                    } else {
                        $('#doc-table-card').removeClass('hidden');
                        $('#empty-state').addClass('hidden').removeClass('flex');
                    }
                }
            });
            window.documentsTable = documentsTable;
        }

        $(document).ready(function() {
            $("#datedoc").datepicker();
            $("#msgfile").hide();
            initsuggest();
            initDocumentsTable();
            loadVolumes();
            initEmailAutocomplete();

            $('#add-btn, #empty-add-btn').on('click', function() {
                openFormModal();
            });
            var searchTimeout;
            $('#search-input').on('keyup', function() {
                var q = $(this).val();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    if (window.documentsTable) window.documentsTable.search(q).draw();
                }, 300);
            });
            $('#formModal').on('hidden.bs.modal', function() {
                $("#frmdata").trigger('reset');
                clearMultipleFileUpload();
                $("#iddoc").val("");
                $('#saveForm').html("เพิ่มข้อมูล").prop('disabled', false);
                $('#formModalTitle').html('<i class="fas fa-file-alt me-2"></i>เพิ่มเอกสาร');
                clearEmailTags();
            });
            $('#formModal').on('shown.bs.modal', function() {
                if ($.fn.selectpicker && $('#doctype').hasClass('selectpicker')) $('#doctype').selectpicker('refresh');
            });
        });

        function initsuggest() {
            var copynumber = 1;
            $('#owner').amsifySuggestags({
                suggestions: [<?= "'" . implode("','", $suggestname) . "'"; ?>],
                defaultTagClass: 'badge',
                type: 'amsify',
                tagLimit: 1,
                showAllSuggestions: false,
                keepLastOnHoverTag: false,
                delimiters: [';']
            });

            $('#participant').amsifySuggestags({
                suggestions: [<?= "'" . implode("','", $suggestname) . "'"; ?>],
                defaultTagClass: 'badge',
                whiteList: false,
                type: 'amsify',
                tagLimit: 100,
                showAllSuggestions: true,
                afterAdd: function(value) {
                    copynumber = copynumber + 1;
                    $('#copynum').val(copynumber);
                },
                afterRemove: function(value) {
                    copynumber = copynumber - 1;
                    $('#copynum').val(copynumber);
                },
            });
        }

        function addtag(flag) {
            if (flag) {
                $('#participant').val("<?php echo "ทุกคน"; ?>");
            } else {
                $('#participant').val("");
            }
            initsuggest();
        }

        // --- Tag Group JS ---
        let tagGroupsData = [];

        function loadTagGroups() {
            $.ajax({
                url: '<?= base_url("index.php/edoc/admin/gettaggroups") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        tagGroupsData = response.data;
                        renderTagGroupSelect();
                    }
                }
            });
        }

        function renderTagGroupSelect() {
            let options = '<option value="">-- เลือกกลุ่ม --</option>';
            tagGroupsData.forEach(group => {
                options += `<option value="${group.id}">${group.name}</option>`;
            });
            $('#tagGroupSelect').html(options);
            $('#btnDeleteGroup').hide();
        }

        $('#tagGroupSelect').on('change', function() {
            const groupId = $(this).val();
            if (!groupId) {
                $('#btnDeleteGroup').hide();
                return;
            }

            $('#btnDeleteGroup').show();
            const group = tagGroupsData.find(g => g.id === groupId);
            if (group) {
                // Determine current tags
                let currentVal = $('#participant').val();
                let currentTags = currentVal ? currentVal.split(',') : [];

                // Add new tags unique
                let newTags = [...currentTags];
                group.tags.forEach(tag => {
                    if (!newTags.includes(tag)) {
                        newTags.push(tag);
                    }
                });

                $('#participant').val(newTags.join(','));

                // Refresh amsify
                initsuggest();
            }
        });

        $('#btnSaveGroup').on('click', function() {
            let currentVal = $('#participant').val();
            if (!currentVal) {
                swalAlert('กรุณาเพิ่มรายชื่อผู้รับก่อนบันทึกกลุ่ม', 'warning');
                return;
            }

            const name = prompt("ตั้งชื่อกลุ่มใหม่:");
            if (name) {
                const tags = currentVal.split(',');
                $.ajax({
                    url: '<?= base_url("index.php/edoc/admin/savetaggroup") ?>',
                    type: 'POST',
                    data: {
                        name: name,
                        tags: tags
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            swalAlert('บันทึกกลุ่มสำเร็จ', 'success');
                            loadTagGroups(); // Reload
                        } else {
                            swalAlert('เกิดข้อผิดพลาด: ' + response.message, 'error');
                        }
                    }
                });
            }
        });

        $('#btnDeleteGroup').on('click', function() {
            const groupId = $('#tagGroupSelect').val();
            if (!groupId) return;

            swalConfirm({ title: 'ต้องการลบกลุ่มนี้ใช่หรือไม่?', confirmText: 'ลบ', cancelText: 'ยกเลิก' }).then(function(ok) {
                if (!ok) return;
                $.ajax({
                    url: '<?= base_url("index.php/edoc/admin/deletetaggroup") ?>',
                    type: 'POST',
                    data: {
                        id: groupId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            swalAlert('ลบกลุ่มสำเร็จ', 'success');
                            loadTagGroups(); // Reload
                            initsuggest(); // Refresh suggested tags to clear selection if we want, but simpler to just leave as is.
                        } else {
                            swalAlert('เกิดข้อผิดพลาด: ' + response.message, 'error');
                        }
                    }
                });
            });
        });

        // Initialize on doc ready
        $(document).ready(function() {
            loadTagGroups();
        });



        // File upload handler
        $('#upload_doc').on('click', function() {
            const files = $('#fileupload').prop('files');

            if (files.length === 0) {
                swalAlert('กรุณาเลือกไฟล์', 'warning');
                return;
            }

            // Show progress
            $('#upload-progress').show();
            $('.progress-bar').css('width', '0%');

            // Upload files sequentially
            uploadFilesSequentially(files, 0);
        });

        function uploadFilesSequentially(files, currentIndex) {
            if (currentIndex >= files.length) {
                // All files uploaded
                $('#upload-progress').hide();
                $('#file-list').hide();
                $('#fileupload').val('');
                $('.custom-file-label').removeClass('selected').html('เลือกไฟล์ (สามารถเลือกหลายไฟล์)');

                updateFileAddressField();
                displayUploadedFiles();

                $('#msgfile').html(`อัปโหลดไฟล์สำเร็จ ${uploadedFiles.length} ไฟล์`).removeClass('alert-danger').addClass('alert-success').show();
                return;
            }

            const file = files[currentIndex];
            const form_data = new FormData();
            form_data.append('file', file);

            // Update progress
            const progress = ((currentIndex) / files.length) * 100;
            $('.progress-bar').css('width', progress + '%');

            $.ajax({
                url: '<?= base_url('index.php/edoc/upload/edoc') ?>',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(response) {
                    if (response.status === 'success' || response.filename) {
                        // Add to uploaded files array
                        uploadedFiles.push({
                            filename: response.filename,
                            original_name: file.name,
                            file_size: file.size,
                            file_type: file.type
                        });
                    }

                    // Upload next file
                    uploadFilesSequentially(files, currentIndex + 1);
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', error);
                    $('#msgfile').html(`เกิดข้อผิดพลาดในการอัปโหลด ${file.name}`).removeClass('alert-success').addClass('alert-danger').show();

                    // Continue with next file
                    uploadFilesSequentially(files, currentIndex + 1);
                }
            });
        }


        // Fixed function to parse fileaddress correctly
        function parseFileAddress(fileaddress) {
            if (!fileaddress || fileaddress.trim() === '') {
                return [];
            }

            let fileList = [];

            try {
                // First, try to parse as JSON array
                if (fileaddress.startsWith('[') && fileaddress.endsWith(']')) {
                    fileList = JSON.parse(fileaddress);
                } else {
                    // Handle comma-separated values with potential quotes
                    fileList = fileaddress.split(',').map(file => {
                        return file.trim()
                            .replace(/^["'\[]/, '') // Remove leading quotes or brackets
                            .replace(/["'\]]$/, '') // Remove trailing quotes or brackets
                            .trim();
                    }).filter(file => file && file.length > 0);
                }
            } catch (e) {
                // Fallback: split by comma and clean
                fileList = fileaddress.split(',').map(file => {
                    return file.trim()
                        .replace(/^["'\[]/, '')
                        .replace(/["'\]]$/, '')
                        .trim();
                }).filter(file => file && file.length > 0);
            }

            // ลบเครื่องหมายและอักขระปลายทางที่ผิดพลาด (เช่น .pdf], [ หรือ quote) ออกจากแต่ละชื่อไฟล์
            return fileList.map(file => {
                return file.replace(/^["'\s\[\]]+|["'\s\[\]]+$/g, '');
            }).filter(file => file && file.length > 0);
        }

        // Updated displayMultipleFilesWithTabs function
        function displayMultipleFilesWithTabs(result_data) {
            const baseUrl = "<?php echo base_url('index.php/edoc/viewPDF/'); ?>";

            // รองรับทั้ง 2 รูปแบบ: ใช้ fileaddress_list จาก API (หน้ารอ่านข้อมูล) หรือ parse เอง
            let fileList = Array.isArray(result_data.fileaddress_list) && result_data.fileaddress_list.length ?
                result_data.fileaddress_list :
                parseFileAddress(result_data.fileaddress);

            console.log('Parsed file list:', fileList); // Debug log

            if (fileList.length === 0) {
                // No files
                updateFileHeader(0);
                $('.pdf-container').html(createNoFileDisplay());
                return;
            }

            // Update header with file count and download options
            updateFileHeader(fileList.length, fileList, baseUrl);

            if (fileList.length === 1) {
                // Single file - display directly without tabs
                displaySingleFile(fileList[0], baseUrl);
            } else {
                // Multiple files - create tabs
                createFileTabsInterface(fileList, baseUrl);
            }
        }

        function edit(id) {
            $('#saveForm').prop('disabled', true);
            $.ajax({
                type: "POST",
                url: "<?= base_url('index.php/edoc/getdocinfo') ?>",
                data: {
                    iddoc: id
                },
                dataType: "json",
                success: function(data) {
                    var result_data = data['result'];
                    $('#officeiddoc').val(result_data.officeiddoc);
                    $('#title').val(result_data.title);
                    $('#doctype').val(result_data.doctype);
                    $('#owner').val(result_data.owner);
                    $('#participant').val(result_data.participant);
                    $('#datedoc').val(result_data.datedoc);
                    $('#pages').val(result_data.pages);
                    $("#iddoc").val(result_data.iddoc);
                    if (result_data.volume_id) {
                        $('#volume_id').val(result_data.volume_id);
                    }
                    clearEmailTags();
                    $.ajax({
                        url: '<?= base_url("index.php/edoc/admin/document-tags") ?>',
                        type: 'GET',
                        data: {
                            iddoc: result_data.iddoc
                        },
                        dataType: 'json',
                        success: function(tagResponse) {
                            if (tagResponse.status === 'success' && tagResponse.data && tagResponse.data.length > 0) {
                                tagResponse.data.forEach(function(tag) {
                                    addEmailTag(tag.tag_email);
                                });
                            }
                        }
                    });
                    uploadedFiles = [];
                    if (result_data.fileaddress || (result_data.fileaddress_list && result_data.fileaddress_list.length)) {
                        var fileList = Array.isArray(result_data.fileaddress_list) && result_data.fileaddress_list.length ?
                            result_data.fileaddress_list :
                            parseFileAddress(result_data.fileaddress || '');
                        fileList.forEach(function(filename) {
                            if (filename) {
                                uploadedFiles.push({
                                    filename: filename,
                                    original_name: filename.split('/').pop() || filename,
                                    file_size: 0,
                                    file_type: ''
                                });
                            }
                        });
                        $("#fileaddress").val(JSON.stringify(fileList));
                        displayUploadedFiles();
                        $('#msgfile').html('พบไฟล์แนบ ' + uploadedFiles.length + ' ไฟล์').removeClass('alert-danger').addClass('alert-info').show();
                    }
                    $('#saveForm').html("ปรับปรุงข้อมูล");
                    $('#saveForm').prop('disabled', false);
                    initsuggest();
                    $('#formModalTitle').html('<i class="fas fa-edit me-2"></i>แก้ไขเอกสาร');
                    $('#formModal').modal('show');
                },
                error: function() {
                    swalAlert('Error loading document data', 'error');
                    $('#saveForm').prop('disabled', false);
                }
            });
        }

        function showToast(message, type) {
            type = type || 'success';
            var cls = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600');
            var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
            var el = $('<div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-white ' + cls + '"><i class="fas ' + icon + ' w-5 h-5"></i><span class="font-medium">' + message + '</span></div>');
            $('#toast-container').append(el);
            setTimeout(function() {
                el.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }

        $('#saveForm').off('click').on('click', function() {
            var datastring = $("#frmdata").serialize();
            $('#saveForm').prop('disabled', true);
            $.ajax({
                type: "POST",
                url: "<?= base_url('index.php/edoc/admin/savedoc') ?>",
                data: datastring,
                dataType: "json",
                success: function(data) {
                    showToast('บันทึกข้อมูลเรียบร้อย');
                    if (window.documentsTable) documentsTable.ajax.reload();
                    $("#frmdata").trigger('reset');
                    clearMultipleFileUpload();
                    clearEmailTags();
                    initsuggest();
                    $("#msgfile").hide();
                    $("#iddoc").val("");
                    $('#saveForm').html("เพิ่มข้อมูล");
                    $('#saveForm').prop('disabled', false);
                    $('#formModal').modal('hide');
                },
                error: function() {
                    swalAlert('พบข้อผิดพลาดกรุณาติดต่อผู้พัฒนา', 'error');
                    $('#saveForm').prop('disabled', false);
                },
                beforeSend: function() {
                    if (uploadedFiles.length === 0) {
                        $('#msgfile').show().html('กรุณาอัปโหลดไฟล์เอกสารอย่างน้อย 1 ไฟล์').removeClass('alert-success alert-info').addClass('alert-danger');
                        $('#saveForm').prop('disabled', false);
                        return false;
                    }
                    updateFileAddressField();
                    var requiredFields = ['officeiddoc', 'title', 'pages', 'doctype', 'datedoc', 'owner', 'participant'];
                    var isEmpty = false;
                    var firstEmptyField = null;
                    for (var i = 0; i < requiredFields.length; i++) {
                        var value = $('#' + requiredFields[i]).val();
                        if (!value || value.trim() === '') {
                            isEmpty = true;
                            if (!firstEmptyField) firstEmptyField = requiredFields[i];
                        }
                    }
                    if (isEmpty) {
                        swalAlert('กรุณาระบุข้อมูลให้ครบถ้วน', 'warning');
                        $('#' + firstEmptyField).focus();
                        $('#saveForm').prop('disabled', false);
                        return false;
                    }
                    return true;
                }
            });
        });

        function clearMultipleFileUpload() {
            uploadedFiles = [];
            $('#fileupload').val('');
            $('.custom-file-label').removeClass('selected').html('เลือกไฟล์ (สามารถเลือกหลายไฟล์)');
            $('#file-list').hide();
            $('#uploaded-files').hide();
            $('#upload-progress').hide();
            $('#selected-files').empty();
            $('#uploaded-files-list').empty();
            $('#fileaddress').val('');
            $('#msgfile').hide().html('');
            $('.progress-bar').css('width', '0%');
            displayUploadedFiles();
        }

        function toggleform() {
            openFormModal();
        }
        // Modify the info function
        function info(id) {
            $.ajax({
                type: "POST",
                url: "<?php echo base_url('index.php/edoc/admin/getdocinfo'); ?>",
                data: {
                    iddoc: id
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        var result_data = response.result;
                        try {
                            // Clear any previous view stats
                            $('#viewStatsSection').remove();

                            // Fill basic document info
                            $('#msg_officeiddoc').text(result_data.officeiddoc || '');
                            $('#msg_title').text(result_data.title || '');
                            $('#msg_doctype').text(result_data.doctype || '');
                            $('#msg_owner').text(result_data.owner || '');
                            $('#msg_participant').text(result_data.participant || '');
                            $('#msg_order').text(result_data.order || '');
                            $('#msg_docdate').text(result_data.datedoc || '');

                            // Add view statistics if available
                            if (result_data.view_statistics && typeof addViewStatistics === 'function') {
                                addViewStatistics(result_data);
                            }

                            // Handle multiple files display with tabs
                            if (typeof displayMultipleFilesWithTabs === 'function') {
                                displayMultipleFilesWithTabs(result_data);
                            } else {
                                $('.pdf-container').html('<p class="text-muted p-3">ไม่พบเอกสารแนบ</p>');
                            }
                        } catch (e) {
                            console.error('info() fill error:', e);
                            if (!$('.pdf-container').html()) {
                                $('.pdf-container').html('<p class="text-muted p-3">โหลดรายละเอียดไม่สมบูรณ์</p>');
                            }
                        }
                        // ย้าย modal ไปที่ body ถ้าอยู่ลึกใน layout (ป้องกัน overflow ตัด)
                        var $modal = $('#modalCenter');
                        if (!$modal.parent().is('body')) {
                            $modal.appendTo('body');
                        }
                        // แสดง modal (รองรับทั้ง Bootstrap 4 และ 5)
                        if (typeof $modal.modal === 'function') {
                            $modal.modal('show');
                        } else if (typeof bootstrap !== 'undefined') {
                            var el = document.getElementById('modalCenter');
                            if (el) new bootstrap.Modal(el).show();
                        } else {
                            $modal.addClass('show').css('display', 'block');
                            $('body').addClass('modal-open');
                            if (!$('.modal-backdrop').length) $('body').append('<div class="modal-backdrop fade show"></div>');
                        }
                    } else {
                        swalAlert(response.message || 'Failed to load document information', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseJSON || xhr.responseText || error);
                    swalAlert("ไม่สามารถโหลดรายละเอียดได้: " + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "กรุณาลองใหม่หรือตรวจสอบการล็อกอิน"), 'error');
                }
            });
        }



        function displayMultipleFilesWithTabs(result_data) {
            // Use viewPDF controller for existing files
            const baseUrl = "<?php echo base_url('index.php/edoc/viewPDF/'); ?>" + result_data.iddoc + "?file=true&subfile=";

            // รองรับทั้ง 2 รูปแบบ: ใช้ fileaddress_list จาก API หรือ parse เอง
            const fileList = Array.isArray(result_data.fileaddress_list) && result_data.fileaddress_list.length ?
                result_data.fileaddress_list :
                parseFileAddress(result_data.fileaddress || '');

            console.log('Parsed file list:', fileList); // Debug log

            if (fileList.length === 0) {
                // No files
                updateFileHeader(0);
                $('.pdf-container').html(createNoFileDisplay());
                return;
            }

            // Update header with file count and download options
            updateFileHeader(fileList.length, fileList, baseUrl);

            if (fileList.length === 1) {
                // Single file - display directly without tabs
                displaySingleFile(fileList[0], baseUrl);
            } else {
                // Multiple files - create tabs
                createFileTabsInterface(fileList, baseUrl);
            }
        }



        function updateFileHeader(fileCount, fileList = [], baseUrl = '') {
            if (fileCount === 0) {
                $('.d-flex.justify-content-between.align-items-center.mb-3').html('<h6 class="mb-0">ไม่มีเอกสารแนบ</h6>');
                return;
            }

            let downloadDropdown = '';
            if (fileCount > 0) {
                downloadDropdown = `
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>ดาวน์โหลด
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    ${fileList.map((file, index) => {
                        const cleanFile = file.trim().replace(/^["'\[]/, '').replace(/["'\]]$/, '');
                        const fileName = cleanFile.split('/').pop() || cleanFile;
                        const fileUrl = baseUrl + cleanFile;
                        return `<a class="dropdown-item" href="${fileUrl}" download>
                            <i class="fas fa-download me-2"></i>${fileName}
                        </a>`;
                    }).join('')}
                    ${fileCount > 1 ? `
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="downloadAllFiles(['${fileList.map(f => f.trim().replace(/^["'\[]/, '').replace(/["'\]]$/, '')).join("','")}'], '${baseUrl}')">
                        <i class="fas fa-download me-2"></i>ดาวน์โหลดทั้งหมด (${fileCount} ไฟล์)
                    </a>` : ''}
                </div>
            </div>
        `;
            }

            const headerHtml = `
        <h6 class="mb-0">
            เอกสารแนบ 
            <span class="badge badge-primary ms-2">${fileCount} ไฟล์</span>
        </h6>
        ${downloadDropdown}
    `;

            $('.d-flex.justify-content-between.align-items-center.mb-3').html(headerHtml);
        }


        function createNoFileDisplay() {
            return `
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-file-slash text-warning" style="font-size: 48px;"></i>
            </div>
            <h5 class="mb-3">ไม่พบไฟล์</h5>
            <p class="mb-4 text-muted">ไม่มีไฟล์แนบหรือไฟล์อาจสูญหาย</p>
        </div>
    `;
        }

        function displaySingleFile(file, baseUrl) {
            const fileUrl = baseUrl + encodeURIComponent(file);
            const fileName = file.split('/').pop() || file;
            const fileExtension = fileName.split('.').pop().toLowerCase();
            const isMobileDevice = isMobile();

            let content = '';

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
                // Image files
                content = `
            <div class="text-center">
                <img src="${fileUrl}" class="img-fluid rounded mx-auto d-block" 
                     style="max-height: ${isMobileDevice ? '400px' : '600px'}; cursor: pointer;" 
                     alt="${fileName}" onclick="openImageModal('${fileUrl}', '${fileName}')">
                <p class="mt-2 text-muted small">${fileName}</p>
            </div>
        `;
            } else if (fileExtension === 'pdf' && !isMobileDevice) {
                // PDF on desktop
                content = `
            <div class="embed-responsive" style="height: 600px;">
                <embed src="${fileUrl}" width="100%" height="100%" type="application/pdf">
            </div>
        `;
            } else {
                // Other files or mobile PDF
                const fileIcon = getFileIcon(fileName);
                content = `
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas ${fileIcon.split(' ')[1]}" style="font-size: 64px; color: #3b82f6;"></i>
                </div>
                <h5 class="mb-3">${fileName}</h5>
                <p class="mb-4 text-muted">
                    ${isMobileDevice && fileExtension === 'pdf' ? 
                        'PDF ไม่สามารถแสดงตัวอย่างบนอุปกรณ์มือถือได้' : 
                        'กรุณาดาวน์โหลดเพื่อดูไฟล์'}
                </p>
                <a href="${fileUrl}" class="btn btn-primary" download>
                    <i class="fas fa-download me-2"></i>ดาวน์โหลด
                </a>
            </div>
        `;
            }

            $('.pdf-container').html(content);
        }


        function createFileTabsInterface(fileList, baseUrl) {
            const tabId = 'fileTabs_' + Date.now();
            const contentId = 'fileTabContent_' + Date.now();

            let tabsHtml = `<ul class="nav nav-tabs mb-3" id="${tabId}" role="tablist">`;
            let contentHtml = `<div class="tab-content" id="${contentId}">`;

            fileList.forEach((file, index) => {
                // Clean the file path
                const cleanFile = file.trim().replace(/^["'\[]/, '').replace(/["'\]]$/, '');
                const fileName = cleanFile.split('/').pop() || cleanFile;
                // Append encoded filename as subfile parameter
                const fileUrl = baseUrl + encodeURIComponent(cleanFile);
                const isActive = index === 0 ? 'active' : '';
                const fileIcon = getFileIcon(fileName);
                const fileExtension = fileName.split('.').pop().toLowerCase();

                console.log(`Tab ${index}: Clean file: ${cleanFile}, URL: ${fileUrl}`); // Debug log

                // Smart truncate filename for tab display
                const displayName = truncateFileName(fileName, 12);

                // Tab header with shortened name
                tabsHtml += `
            <li class="nav-item" role="presentation">
                <a class="nav-link ${isActive}" id="file-tab-${index}" 
                   data-bs-toggle="tab" data-toggle="tab" href="#file-content-${index}" 
                   role="tab" aria-controls="file-content-${index}" 
                   title="${fileName}">
                    <i class="fas ${fileIcon.split(' ')[1]} me-2"></i>
                    <span class="d-none d-md-inline">${displayName}</span>
                    <span class="d-md-none">${index + 1}</span>
                </a>
            </li>
        `;

                // Tab content with full filename display
                let fileContentHtml = '';

                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
                    // Image display
                    fileContentHtml = `
                <div class="text-center">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">${fileName}</h6>
                        <small class="text-muted">กรุณาดาวน์โหลดเพื่อดูไฟล์</small>
                    </div>
                    <img src="${fileUrl}" class="img-fluid rounded mx-auto d-block" 
                         style="max-height: 400px; cursor: pointer;" 
                         alt="${fileName}" onclick="openImageModal('${fileUrl}', '${fileName}')">
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="downloadFile('${fileUrl}', '${fileName}')">
                            <i class="fas fa-download me-2"></i>ดาวน์โหลด
                        </button>
                    </div>
                </div>
            `;
                } else if (fileExtension === 'pdf' && !isMobile()) {
                    // PDF embed for desktop
                    fileContentHtml = `
                <div class="text-center mb-3">
                    <h6 class="text-muted mb-2">${fileName}</h6>
                    <small class="text-muted">กรุณาดาวน์โหลดเพื่อดูไฟล์</small>
                </div>
                <div class="embed-responsive" style="height: 450px;">
                    <embed src="${fileUrl}" width="100%" height="100%" type="application/pdf">
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-primary" onclick="downloadFile('${fileUrl}', '${fileName}')">
                        <i class="fas fa-download me-2"></i>ดาวน์โหลด
                    </button>
                </div>
            `;
                } else {
                    // Other files or mobile PDF
                    fileContentHtml = `
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas ${fileIcon.split(' ')[1]}" style="font-size: 48px; color: #3b82f6;"></i>
                    </div>
                    <h6 class="mb-2">${fileName}</h6>
                    <p class="mb-3 text-muted">
                        ${isMobile() && fileExtension === 'pdf' ? 
                            'PDF ไม่สามารถแสดงตัวอย่างบนอุปกรณ์มือถือได้' : 
                            'กรุณาดาวน์โหลดเพื่อดูไฟล์'}
                    </p>
                    <button class="btn btn-primary" onclick="downloadFile('${fileUrl}', '${fileName}')">
                        <i class="fas fa-download me-2"></i>ดาวน์โหลด
                    </button>
                </div>
            `;
                }

                contentHtml += `
            <div class="tab-pane fade ${isActive ? 'show active' : ''}" 
                 id="file-content-${index}" role="tabpanel" 
                 aria-labelledby="file-tab-${index}">
                ${fileContentHtml}
            </div>
        `;
            });

            tabsHtml += '</ul>';
            contentHtml += '</div>';

            $('.pdf-container').html(tabsHtml + contentHtml);
        }



        // Helper function to download individual files
        function downloadFile(url, filename) {
            if (isMobile()) {
                window.open(url, '_blank');
            } else {
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
            }
        }

        function truncateFileName(filename, maxLength) {
            if (filename.length <= maxLength) {
                return filename;
            }

            // Split filename and extension
            const lastDotIndex = filename.lastIndexOf('.');
            if (lastDotIndex === -1) {
                // No extension
                return filename.substring(0, maxLength - 3) + '...';
            }

            const name = filename.substring(0, lastDotIndex);
            const extension = filename.substring(lastDotIndex);

            // Calculate available space for name (reserve space for extension and ...)
            const availableSpace = maxLength - extension.length - 3;

            if (availableSpace <= 0) {
                // If extension is too long, just truncate the whole filename
                return filename.substring(0, maxLength - 3) + '...';
            }

            return name.substring(0, availableSpace) + '...' + extension;
        }

        // Updated downloadAllFiles function
        function downloadAllFiles(fileList, baseUrl) {
            fileList.forEach((file, index) => {
                setTimeout(() => {
                    const cleanFile = file.trim().replace(/^["'\[]/, '').replace(/["'\]]$/, '');
                    const fileUrl = baseUrl + encodeURIComponent(cleanFile);
                    const fileName = cleanFile.split('/').pop() || cleanFile;
                    downloadFile(fileUrl, fileName);
                }, index * 500); // Delay between downloads
            });
        }
        // Function to open image in modal (optional enhancement)
        function openImageModal(imageUrl, imageName) {
            // Create image modal if not exists
            if (!$('#imageModal').length) {
                $('body').append(`
            <div class="modal fade" id="imageModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="imageModalImg" src="" class="img-fluid w-100">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                            <a id="imageDownloadBtn" href="" download class="btn btn-primary">
                                <i class="fas fa-download me-1"></i>ดาวน์โหลด
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `);
            }

            // Set image and show modal
            $('#imageModalTitle').text(imageName);
            $('#imageModalImg').attr('src', imageUrl);
            $('#imageDownloadBtn').attr('href', imageUrl).attr('download', imageName);
            $('#imageModal').modal('show');
        }

        // Add view statistics function
        function addViewStatistics(result_data) {
            if (!result_data.view_statistics) return;

            const viewStatsHtml = `
        <div id="viewStatsSection" class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex align-items-center mb-3">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            สถิติการเข้าชม
                        </h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="far fa-eye text-white"></i>
                                                </div>
                                                <div>
                                                    <h3 class="mb-0">${result_data.view_statistics.total_views}</h3>
                                                    <small class="text-muted">จำนวนการเข้าชมทั้งหมด</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="far fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <h3 class="mb-0">${result_data.view_statistics.unique_viewers}</h3>
                                                    <small class="text-muted">ผู้เข้าชมที่ไม่ซ้ำกัน</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewAllStats('${result_data.iddoc}')">
                                <i class="fas fa-list me-1"></i> แสดงประวัติทั้งหมด
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            $('.d-flex.justify-content-between.align-items-center.mb-3').before(viewStatsHtml);
        }


        function downloadFile(url, filename) {
            if (isMobile()) {
                // On mobile, it's better to open in a new tab
                window.open(url, '_blank');
            } else {
                // On desktop, we can use the Fetch API
                fetch(url)
                    .then(response => response.blob())
                    .then(blob => {
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = filename || 'document';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(blobUrl);
                        a.remove();
                    })
                    .catch(error => {
                        console.error('Download error:', error);
                        swalAlert('An error occurred while downloading the file. Please try again.', 'error');
                        // Fallback to direct link
                        window.open(url, '_blank');
                    });
            }
        }


        function viewAllStats(docId) {
            $.ajax({
                type: "POST",
                url: "<?php echo base_url('index.php/edoc/getallviewers'); ?>",
                data: {
                    iddoc: docId
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        const allViewers = response.viewers;

                        // Create the modal content
                        let tableRows = '';

                        if (allViewers && allViewers.length > 0) {
                            allViewers.forEach((viewer, index) => {
                                const viewerName = viewer.thai_name && viewer.thai_lastname ?
                                    `${viewer.thai_name} ${viewer.thai_lastname}` :
                                    'ผู้ใช้ไม่ระบุชื่อ';

                                const viewDate = new Date(viewer.viewed_at);
                                const formattedDateTime = viewDate.toLocaleString('th-TH', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit'
                                });

                                const userType = viewer.user_id ?
                                    '<span class="badge bg-success">ผู้ใช้ลงทะเบียน</span>' :
                                    '<span class="badge bg-secondary">ผู้ใช้ไม่ลงทะเบียน</span>';

                                tableRows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${viewerName}</td>
                            <td>${userType}</td>
                            <td>${formattedDateTime}</td>
                            <td>${viewer.ip_address}</td>
                        </tr>`;
                            });
                        } else {
                            tableRows = '<tr><td colspan="5" class="text-center">ไม่พบข้อมูลการเข้าชม</td></tr>';
                        }

                        const modalContent = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>ประเภท</th>
                                <th>เวลาที่เข้าชม</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                </div>`;

                        // Show the modal
                        $('#statsModalTitle').text('ประวัติการเข้าชมทั้งหมด');
                        $('#statsModalBody').html(modalContent);
                        $('#statsModal').modal('show');
                    } else {
                        swalAlert(response.message || 'Failed to load viewing history', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseJSON || error);
                    swalAlert("An error occurred while fetching viewing history. Please try again.", 'error');
                }
            });
        }

        // Helper function to format recent viewers list
        function getRecentViewersHtml(recentViewers) {
            if (!recentViewers || recentViewers.length === 0) {
                return '<div class="list-group-item px-0 border-0">ยังไม่มีการเข้าชม</div>';
            }

            let html = '';

            // Create detailed list of viewers
            recentViewers.forEach((viewer, index) => {
                const viewerName = viewer.thai_name && viewer.thai_lastname ?
                    `${viewer.thai_name} ${viewer.thai_lastname}` :
                    'ผู้ใช้ไม่ระบุชื่อ';

                const viewDate = new Date(viewer.viewed_at);
                const formattedDate = viewDate.toLocaleDateString('th-TH', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                const formattedTime = viewDate.toLocaleTimeString('th-TH', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const ipAddress = viewer.ip_address || '';

                html += `
        <div class="list-group-item px-0 ${index < recentViewers.length - 1 ? 'border-bottom' : 'border-0'}">
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="far fa-user-circle text-primary me-2"></i>
                        <span class="fw-medium">${viewerName}</span>
                    </div>
                    ${viewer.user_id ? '<span class="badge bg-success ms-2">Registered</span>' : '<span class="badge bg-secondary ms-2">Guest</span>'}
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted">
                        <i class="far fa-clock me-1"></i> ${formattedDate} เวลา ${formattedTime}
                    </small>
                    <small class="text-muted">
                        <i class="fas fa-network-wired me-1"></i> ${ipAddress}
                    </small>
                </div>
            </div>
        </div>`;
            });

            return html;
        }

        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        function downloadPDF(url, filename) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename || 'document.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(blobUrl);
                    a.remove();
                });
        }

        // Update the link click handler
        document.getElementById('msg_fileaddresslink').onclick = function(e) {
            e.preventDefault();
            const fileUrl = this.getAttribute('href');
            const filename = fileUrl.split('/').pop();
            downloadPDF(fileUrl, filename);
        };

        function parseFileAddress(fileaddress) {
            if (!fileaddress || fileaddress.trim() === '') {
                return [];
            }

            let fileList = [];

            try {
                // First, try to parse as JSON array
                if (fileaddress.startsWith('[') && fileaddress.endsWith(']')) {
                    fileList = JSON.parse(fileaddress);
                } else {
                    // Handle comma-separated values with potential quotes
                    fileList = fileaddress.split(',').map(file => {
                        return file.trim()
                            .replace(/^["'\[]/, '') // Remove leading quotes or brackets
                            .replace(/["'\]]$/, '') // Remove trailing quotes or brackets
                            .trim();
                    }).filter(file => file && file.length > 0);
                }
            } catch (e) {
                // Fallback: split by comma and clean
                fileList = fileaddress.split(',').map(file => {
                    return file.trim()
                        .replace(/^["'\[]/, '')
                        .replace(/["'\]]$/, '')
                        .trim();
                }).filter(file => file && file.length > 0);
            }

            // ลบเครื่องหมายและอักขระปลายทางที่ผิดพลาด (เช่น .pdf], [ หรือ quote) ออกจากแต่ละชื่อไฟล์
            return fileList.map(file => {
                return file.replace(/^["'\s\[\]]+|["'\s\[\]]+$/g, '');
            }).filter(file => file && file.length > 0);
        }
    </script>
</body>

</html>