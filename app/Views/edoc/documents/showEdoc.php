<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการเอกสาร</title>

    <!-- Favicon -->
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>">

    <!-- Bootstrap CSS (for modal functionality) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        /* Custom DataTables Styling */
        .dataTables_wrapper {
            padding: 0 1rem 1rem;
        }

        .dataTables_filter {
            display: none;
        }

        .dataTables_length {
            margin: 0.75rem 0 0.5rem;
            padding: 0 0.25rem;
        }
        .dataTables_length select {
            padding: 0.4rem 0.6rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: #fff;
            font-size: 0.875rem;
        }

        table.dataTable {
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        table.dataTable thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.8125rem;
            color: #475569;
        }

        table.dataTable tbody td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        table.dataTable tbody td .doc-participant-chips {
            flex-wrap: nowrap;
            overflow: hidden;
            min-width: 0;
        }

        table.dataTable tbody tr:hover {
            background: #f8fafc;
            cursor: pointer;
        }

        table.dataTable tbody tr:last-child td {
            border-bottom-color: #e2e8f0;
        }

        .dataTables_paginate {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.25rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem;
            margin: 0;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .dataTables_paginate .paginate_button.current {
            background: #4f46e5;
            border-color: #4f46e5;
            color: white;
        }
        .dataTables_info {
            color: #64748b;
            font-size: 0.8125rem;
            margin-top: 0.75rem;
        }

        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.25;
        }

        .doc-participant-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            align-items: center;
        }
        .doc-chip {
            white-space: nowrap;
            max-width: 10em;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .doc-chip-user {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .doc-chip-everyone {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }
        .doc-chip-owner {
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #a5b4fc;
        }
        .doc-chip-more {
            background: #f1f5f9;
            color: #64748b;
            border: 1px dashed #94a3b8;
            cursor: help;
        }

        /* Toolbar controls */
        .edoc-input {
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .edoc-btn-secondary {
            background: #4f46e5;
            color: #fff;
            border: 1px solid #4f46e5;
        }
        .edoc-btn-secondary:hover {
            background: #4338ca;
            border-color: #4338ca;
            color: #fff;
        }
        .edoc-btn-ghost {
            background: transparent;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }
        .edoc-btn-ghost:hover {
            background: #f9fafb;
            color: #374151;
            border-color: #d1d5db;
        }
        .edoc-table-card {
            background: #fff;
        }
        #fetch_users thead th {
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: none;
            letter-spacing: 0.01em;
        }
        #fetch_users tbody td {
            font-size: 0.875rem;
        }
        #fetch_users tbody tr {
            display: table-row;
        }
        #fetch_users tbody td:nth-child(1) { max-width: 4rem; }
        #fetch_users tbody td:nth-child(2) { max-width: 8rem; }
        #fetch_users tbody td:nth-child(3) { max-width: 14rem; }
        #fetch_users tbody td:nth-child(4) { max-width: 10rem; }
        #fetch_users tbody td:nth-child(5) { max-width: 8rem; }
        #fetch_users tbody td:nth-child(6) { max-width: 12rem; }
        #fetch_users tbody td:nth-child(7) { max-width: 7rem; }
        #fetch_users tbody td:nth-child(8) { max-width: 4rem; }
        /* Tab ชนิดหนังสือ */
        .doc-type-tabs .doc-type-tab {
            color: #4b5563;
        }
        .doc-type-tabs .doc-type-tab:hover {
            color: #4f46e5;
            background: rgba(255,255,255,0.8);
        }
        .doc-type-tabs .doc-type-tab.active {
            background: #fff;
            color: #4f46e5;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .status-report {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-memo {
            background: #dcfce7;
            color: #166534;
        }

        .status-invitation {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .status-plan {
            background: #fef3c7;
            color: #92400e;
        }

        .status-urgent {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-approved {
            background: #d1fae5;
            color: #059669;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-rejected {
            background: #fecaca;
            color: #dc2626;
        }

        .status-draft {
            background: #e5e7eb;
            color: #6b7280;
        }

        .status-completed {
            background: #ccfbf1;
            color: #0f766e;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .status-review {
            background: #ddd6fe;
            color: #7c3aed;
        }

        .status-internal {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-external {
            background: #fdf2f8;
            color: #be185d;
        }

        .status-confidential {
            background: #f1f5f9;
            color: #475569;
        }

        .status-public {
            background: #ecfdf5;
            color: #047857;
        }

        .status-meeting {
            background: #fff7ed;
            color: #ea580c;
        }

        .status-announcement {
            background: #fefce8;
            color: #ca8a04;
        }

        .status-regulation {
            background: #f8fafc;
            color: #334155;
        }

        .status-policy {
            background: #fdf4ff;
            color: #a21caf;
        }

        .status-procedure {
            background: #f0f9ff;
            color: #0284c7;
        }

        .status-circular {
            background: #f0fdf4;
            color: #16a34a;
        }

        .status-notice {
            background: #fffbeb;
            color: #d97706;
        }

        .status-directive {
            background: #fef2f2;
            color: #ef4444;
        }

        .status-default {
            background: #f3f4f6;
            color: #374151;
        }

        /* Footer: แถบกรองคอลัมน์ */
        #fetch_users tfoot th {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 0.5rem 1rem;
            vertical-align: middle;
        }
        #fetch_users tfoot input {
            width: 100%;
            padding: 0.4rem 0.6rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            background: #fff;
        }
        #fetch_users tfoot input::placeholder {
            color: #94a3b8;
        }
        #fetch_users tfoot input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
        }

        /* Loading animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Custom modal styling */
        .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            border-radius: 0.75rem 0.75rem 0 0;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Document info styling */
        .doc-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .doc-info-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .doc-info-value {
            color: #1e293b;
            font-weight: 500;
        }

        /* PDF container */
        .pdf-container {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dataTables_paginate {
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .dataTables_info {
                text-align: center;
                margin: 1rem 0;
            }

            .content-page {
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Loading overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center">
            <div class="loading-spinner mr-3"></div>
            <span>กำลังโหลดข้อมูล...</span>
        </div>
    </div>

    <div class="wrapper">
        <!-- Sidebar -->
        <div class="iq-sidebar sidebar-default" id="mainMenu"></div>

        <!-- Main Content -->
        <div class="content-page">
            <div class="container-fluid p-4 md:p-6">
                <!-- Header: ชื่อหน้า + คำอธิบายสั้น -->
                <div class="mb-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">ระบบจัดการเอกสาร</h1>
                    <p class="text-gray-500 mt-1 text-sm md:text-base">ติดตามและจัดการเอกสารทั้งหมดของคุณ</p>
                </div>

                <!-- Card หลัก: Toolbar + ตาราง -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                    <!-- Toolbar: ค้นหา + ชนิดหนังสือ + ปี + ปุ่ม -->
                    <div class="p-4 md:p-5 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1 min-w-0">
                                    <div class="relative flex-1 sm:max-w-xs">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                            <i class="fas fa-search text-sm"></i>
                                        </span>
                                        <input type="text" id="globalSearch" placeholder="ค้นหาเอกสาร..."
                                            class="edoc-input w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                    </div>
                                    <button type="button" id="btn-advanced-search" class="edoc-btn-secondary inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl whitespace-nowrap">
                                        <i class="fas fa-sliders-h"></i>
                                        <span id="btn-advanced-search-text">ค้นหาละเอียด</span>
                                    </button>
                                    <button id="clearFiltersBtn" class="edoc-btn-ghost inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl whitespace-nowrap">
                                        <i class="fas fa-redo-alt"></i>
                                        เคลียร์ตัวกรอง
                                    </button>
                                </div>
                            </div>
                            <!-- แถบชนิดหนังสือ + ปี -->
                            <div id="edoc-type-year-bar" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-gray-200/80">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-gray-500">ชนิดหนังสือ</span>
                                    <div class="doc-type-tabs flex flex-wrap gap-1 rounded-xl bg-gray-100 p-1.5" role="tablist">
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all active" data-doctype="" role="tab">ทั้งหมด</button>
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all" data-doctype="หนังสือรับภายใน" role="tab">รับภายใน</button>
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all" data-doctype="หนังสือรับภายนอก" role="tab">รับภายนอก</button>
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all" data-doctype="หนังสือส่งภายใน" role="tab">ส่งภายใน</button>
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all" data-doctype="ใบลา" role="tab">ใบลา</button>
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all" data-doctype="คำสั่ง" role="tab">คำสั่ง</button>
                                        <button type="button" class="doc-type-tab px-3 py-2 text-sm font-medium rounded-lg transition-all" data-doctype="ประกาศ" role="tab">ประกาศ</button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 sm:pl-2">
                                    <label class="text-sm font-medium text-gray-500">ปี</label>
                                    <select id="edoc-year-select" class="edoc-input rounded-xl border border-gray-200 px-3 py-2 text-sm bg-white min-w-[5.5rem] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                        <option value="">ทุกปี</option>
                                        <?php if (!empty($availableYears)): ?>
                                            <?php foreach ($availableYears as $y): ?>
                                                <option value="<?= (int)$y ?>" <?= ($y == ($currentYear ?? (date('Y') + 543))) ? 'selected' : '' ?>><?= (int)$y ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <select id="yearFilter" class="hidden">
                                        <option value="">ทุกปี</option>
                                        <?php if (!empty($availableYears)): ?>
                                            <?php foreach ($availableYears as $year): ?>
                                                <option value="<?= $year ?>" <?= (isset($currentYear) && $currentYear == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- Advanced Search Panel -->
                        <div id="advanced-search-panel" class="hidden mb-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                    ค้นหาละเอียด
                                </h2>
                                <button type="button" id="btn-close-advanced-search" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm">ปิด</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทเอกสาร</label>
                                    <select id="adv-doctype" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option value="">ทุกประเภท</option>
                                        <option value="หนังสือรับภายใน">หนังสือรับภายใน</option>
                                        <option value="หนังสือรับภายนอก">หนังสือรับภายนอก</option>
                                        <option value="หนังสือส่งภายใน">หนังสือส่งภายใน</option>
                                        <option value="ใบลา">ใบลา</option>
                                        <option value="คำสั่ง">คำสั่ง</option>
                                        <option value="ประกาศ">ประกาศ</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ปี</label>
                                    <select id="adv-year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option value="">ทุกปี</option>
                                        <?php if (!empty($availableYears)): ?>
                                            <?php foreach ($availableYears as $y): ?>
                                            <option value="<?= (int)$y ?>" <?= ($y == ($currentYear ?? (date('Y') + 543))) ? 'selected' : '' ?>><?= (int)$y ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เล่ม</label>
                                    <select id="adv-volume" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option value="">ทุกเล่ม</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">วันที่เริ่ม</label>
                                    <input type="date" id="adv-date-from" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">วันที่สิ้นสุด</label>
                                    <input type="date" id="adv-date-to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div class="md:col-span-2 flex items-end">
                                    <button type="button" id="adv-btn-search" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">ค้นหา</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขที่หนังสือ</label>
                                    <input type="text" id="adv-officeiddoc" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="ส่วนของเลขที่">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เจ้าของเอกสาร</label>
                                    <input type="text" id="adv-owner" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="ชื่อเจ้าของ">
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="adv-btn-clear" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg">ล้างตัวกรอง</button>
                            </div>
                            <div id="advanced-search-badges" class="mt-3 flex flex-wrap gap-2 hidden"></div>
                        </div>

                        <!-- DataTable Container -->
                        <div class="edoc-table-card overflow-hidden">
                            <div class="overflow-x-auto custom-scrollbar">
                                <table id="fetch_users" class="w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>เลขที่หนังสือ</th>
                                            <th>ชื่อเรื่อง</th>
                                            <th>ชนิดเอกสาร</th>
                                            <th>เจ้าของเอกสาร</th>
                                            <th>ผู้มีส่วนร่วม</th>
                                            <th>วันที่ลงหนังสือ</th>
                                            <th>คำสั่งการ</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" placeholder="กรอง เลขที่"></th>
                                            <th><input type="text" placeholder="กรอง ชื่อเรื่อง"></th>
                                            <th><input type="text" placeholder="กรอง ชนิด"></th>
                                            <th><input type="text" placeholder="กรอง เจ้าของ"></th>
                                            <th><input type="text" placeholder="กรอง ผู้มีส่วนร่วม"></th>
                                            <th><input type="text" placeholder="กรอง วันที่"></th>
                                            <th><input type="text" placeholder="กรอง คำสั่ง"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Detail Modal -->
    <div class="modal fade" id="modalCenter" tabindex="-1" aria-labelledby="modalCenterLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterLabel">รายละเอียดเอกสาร</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Document Info Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="doc-info-card">
                                <div class="doc-info-label">เลขที่หนังสือ</div>
                                <div class="doc-info-value" id="msg_officeiddoc">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="doc-info-card">
                                <div class="doc-info-label">วันที่ลงหนังสือ</div>
                                <div class="doc-info-value" id="msg_docdate">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="doc-info-card">
                                <div class="doc-info-label">ชนิดเอกสาร</div>
                                <div class="doc-info-value" id="msg_doctype">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="doc-info-card">
                                <div class="doc-info-label">เจ้าของเอกสาร</div>
                                <div class="doc-info-value" id="msg_owner">-</div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="doc-info-card">
                                <div class="doc-info-label">ชื่อเรื่อง</div>
                                <div class="doc-info-value" id="msg_title">-</div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="doc-info-card">
                                <div class="doc-info-label">ผู้เกี่ยวข้อง</div>
                                <div class="doc-info-value" id="msg_participant">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="doc-info-card">
                                <div class="doc-info-label">คำสั่งการ</div>
                                <div class="doc-info-value" id="msg_order">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- File Section -->
                    <div class="border-top pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">เอกสารแนบ</h6>
                            <div class="d-flex gap-2">
                                <button id="printBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-print me-1"></i>
                                    พิมพ์
                                </button>
                                <a id="msg_fileaddresslink" href="#"
                                    class="btn btn-primary btn-sm d-none">
                                    <i class="fas fa-download me-1"></i>
                                    ดาวน์โหลด
                                </a>
                            </div>
                        </div>

                        <div class="pdf-container">
                            <div class="text-center text-muted">
                                <i class="fas fa-file-pdf fa-3x mb-3"></i>
                                <p>กำลังโหลดไฟล์...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Modal -->
    <div class="modal fade" id="statsModal" tabindex="-1" aria-labelledby="statsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statsModalTitle">สถิติการเข้าชม</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="statsModalBody">
                    <!-- Statistics content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/backend-bundle.min.js') ?>"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script type="text/javascript">
        function closeModal() {
            // Force close all modals - simple and reliable
            $('.modal').each(function() {
                $(this).removeClass('show');
                $(this).hide();
                $(this).attr('aria-hidden', 'true');
                $(this).removeAttr('aria-modal');
                $(this).removeAttr('style');
            });

            // Remove backdrop
            $('.modal-backdrop').remove();

            // Clean body
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        }

        // Sidebar not used in newScience integration
        // $("#mainMenu").load("");

        let table;

        $(document).ready(function() {
            // Sync แถบชนิดหนังสือ + ปี กับตัวกรอง (เหมือน Admin) — ใช้ค่าตอนโหลดครั้งแรก
            if ($('#edoc-year-select').length) {
                var y = $('#edoc-year-select').val();
                $('#adv-year').val(y);
                $('#yearFilter').val(y);
            }
            if ($('.doc-type-tab.active').length) {
                $('#adv-doctype').val($('.doc-type-tab.active').data('doctype') || '');
            }
            var table = $('#fetch_users').DataTable({
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                "order": [
                    [0, "desc"]
                ],
                "ordering": true, // Enable sorting
                "language": {
                    "processing": "กำลังประมวลผล...",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "search": "ค้นหา:",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "ajax": {
                    "url": "<?php echo base_url() . 'index.php/edoc/getdoc' ?>",
                    "type": "POST",
                    "data": function(d) {
                        // Add custom search parameters for each column
                        d.columnSearch = [];
                        $('tfoot input').each(function(i) {
                            if (this.value.length > 0) {
                                d.columnSearch.push({
                                    column: i,
                                    search: this.value
                                });
                            }
                        });
                        // ชนิดหนังสือ + ปี จากแถบ (หรือจาก advanced)
                        if ($('#edoc-year-select').length) {
                            var barYear = $('#edoc-year-select').val();
                            if (barYear) d.doc_year = barYear;
                        } else {
                            var yearVal = $('#yearFilter').val();
                            if (yearVal) d.doc_year = yearVal;
                        }
                        d.doctype = ($('.doc-type-tabs .doc-type-tab.active').length ? ($('.doc-type-tabs .doc-type-tab.active').data('doctype') || '') : ($('#adv-doctype').val() || ''));
                        if ($('#adv-year').val()) d.doc_year = $('#adv-year').val();
                        d.volume_id = $('#adv-volume').val() || '';
                        d.date_from = $('#adv-date-from').val() || '';
                        d.date_to = $('#adv-date-to').val() || '';
                        d.filter_owner = $('#adv-owner').val() || '';
                        d.filter_officeiddoc = $('#adv-officeiddoc').val() || '';
                    },
                    "dataSrc": function(json) {
                        if (!json.records) {
                            console.error('Server response does not contain "records" array:', json);
                            return [];
                        }
                        return json.records;
                    }
                },
                "columns": [{
                        "data": "iddoc",
                        "defaultContent": "",
                        "render": function(data) { return data != null ? data : ''; }
                    },
                    {
                        "data": "officeiddoc",
                        "defaultContent": "",
                        "render": function(data, type) {
                            if (type !== 'display' || data == null) return data;
                            var text = (typeof data === 'string' && data.indexOf('<') !== -1) ? $('<div>').html(data).text().trim() : data;
                            return (text + '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        }
                    },
                    {
                        "data": "title",
                        "defaultContent": "-",
                        "render": function(data, type) {
                            if (type !== 'display') return data;
                            if (data == null) return '-';
                            var text = (typeof data === 'string' && data.indexOf('<') !== -1) ? $('<div>').html(data).text().trim() : data;
                            return (text + '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        }
                    },
                    {
                        "data": "doctype",
                        "defaultContent": "",
                        "render": function(data) { return data != null ? (data + '').replace(/</g, '&lt;').replace(/>/g, '&gt;') : ''; }
                    },
                    {
                        "data": "owner",
                        "defaultContent": "",
                        "render": function(data, type, row) {
                            if (type === 'display' && data) {
                                var label = (data + '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                return '<span class="doc-chip doc-chip-owner status-badge" title="เจ้าของเอกสาร">' + label + '</span>';
                            }
                            return data || '';
                        }
                    },
                    {
                        "data": "participant",
                        "defaultContent": "",
                        "render": function(data, type, row) {
                            if (type === 'display' && row.participant_chips && row.participant_chips.length) {
                                var chips = row.participant_chips;
                                var maxChips = 3;
                                var show = chips.slice(0, maxChips);
                                var rest = chips.length - maxChips;
                                var html = '<div class="doc-participant-chips flex flex-wrap gap-1">';
                                show.forEach(function(chip) {
                                    var label = (chip.name || chip.email || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                    var title = (chip.email && chip.email !== chip.name) ? (' title="' + (chip.email || '').replace(/"/g, '&quot;') + '"') : '';
                                    var cls = chip.email === 'ทุกคน' ? 'doc-chip doc-chip-everyone' : 'doc-chip doc-chip-user';
                                    html += '<span class="status-badge ' + cls + '"' + title + '>' + label + '</span>';
                                });
                                if (rest > 0) {
                                    var fullList = chips.map(function(c) { return (c.name || c.email || ''); }).join('\n');
                                    html += '<span class="status-badge doc-chip doc-chip-more" title="' + (fullList.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')) + '">+' + rest + ' คน</span>';
                                }
                                html += '</div>';
                                return html;
                            }
                            return (data || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        }
                    },
                    {
                        "data": "datedoc",
                        "defaultContent": "",
                        "render": function(data) { return data != null ? formatDateToThai(data) : ''; }
                    },
                    {
                        "data": "order",
                        "defaultContent": "",
                        "render": function(data) { return data != null ? data : ''; }
                    }
                ],
                "columnDefs": [{
                        targets: [6, 5],
                        render: function(data, type, row) {
                            return type === 'display' && data && data.length > 10 ?
                                data.substr(0, 15) + '…' :
                                data;
                        }
                    },
                    {
                        targets: [3, 4],
                        render: function(data, type, row) {
                            return type === 'display' && data && data.length > 10 ?
                                data.substr(0, 20) + '…' :
                                data;
                        }
                    },
                    {
                        "targets": 3,
                        "type": "date-eu"
                    }
                ],
                "initComplete": function() {
                    this.api().columns().every(function() {
                        var that = this;
                        $('input', this.footer()).on('keyup', function(e) {
                            if (e.key === 'Enter') {
                                if (that.search() !== this.value) {
                                    that.search(this.value).draw();
                                }
                            }
                        });
                    });
                },
                "drawCallback": function(settings) {
                    if (typeof updateAdvFilterBadgesUser === 'function') updateAdvFilterBadgesUser();
                    if (typeof updateAdvSearchButtonTextUser === 'function') updateAdvSearchButtonTextUser();
                },
                "error": function(xhr, error, thrown) {
                    console.error('DataTables error:', error, thrown);
                }
            });

            // คลิกแถวเพื่อเปิดรายละเอียดเอกสาร
            $('#fetch_users').on('click', 'tbody tr', function() {
                var row = table.row(this).data();
                if (row && row.iddoc) info(row.iddoc);
            });

            // Add the following code to handle the column search
            $('#fetch_users tfoot th').each(function(i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Search ' + title + '" />');
            });

            // Add a button to clear all column filters
            // $('#fetch_users_wrapper').prepend('<button id="clear-filters" class="btn btn-secondary mb-3">Clear All Filters</button>');

            // Clear all filters when the button is clicked (รวมแถบชนิดหนังสือ + ปี)
            $('#clearFiltersBtn').on('click', function() {
                $('tfoot input').val('');
                $('#globalSearch').val('');
                $('#yearFilter').val('');
                if ($('#edoc-year-select').length) {
                    var currentY = '<?= (int)($currentYear ?? (date("Y") + 543)) ?>';
                    $('#edoc-year-select').val(currentY);
                    $('#adv-year').val(currentY);
                    $('#yearFilter').val(currentY);
                }
                $('.doc-type-tab').removeClass('active').first().addClass('active');
                $('#adv-doctype').val('');
                table.columns().search('').draw();
                table.search('').draw();
                if (table.ajax) table.ajax.reload();
                if (typeof updateAdvFilterBadgesUser === 'function') updateAdvFilterBadgesUser();
                if (typeof updateAdvSearchButtonTextUser === 'function') updateAdvSearchButtonTextUser();
            });

            $('#globalSearch').on('keyup change', function() {
                table.search(this.value).draw();
            });

            // Year filter change - reload table และ sync กับแถบปี
            $('#yearFilter').on('change', function() {
                var v = $(this).val();
                if ($('#edoc-year-select').length) $('#edoc-year-select').val(v);
                $('#adv-year').val(v);
                table.ajax.reload();
            });

            // Tab ชนิดหนังสือ + ปี: sync กับ adv filter แล้ว reload (เหมือน Admin)
            function applyTypeYearAndReloadUser() {
                var doctype = $('.doc-type-tabs .doc-type-tab.active').data('doctype') || '';
                var year = $('#edoc-year-select').length ? $('#edoc-year-select').val() : $('#adv-year').val();
                $('#adv-doctype').val(doctype);
                $('#adv-year').val(year);
                $('#yearFilter').val(year);
                if (table && table.ajax) table.ajax.reload();
                if (typeof updateAdvFilterBadgesUser === 'function') updateAdvFilterBadgesUser();
                if (typeof updateAdvSearchButtonTextUser === 'function') updateAdvSearchButtonTextUser();
            }
            $('.doc-type-tab').on('click', function() {
                $('.doc-type-tab').removeClass('active');
                $(this).addClass('active');
                applyTypeYearAndReloadUser();
            });
            $('#edoc-year-select').on('change', function() {
                var v = $(this).val();
                $('#adv-year').val(v);
                $('#yearFilter').val(v);
                applyTypeYearAndReloadUser();
            });

            // Advanced Search Panel
            function loadAdvVolumesUser(year) {
                year = year || $('#adv-year').val() || (new Date().getFullYear() + 543); // default ปี พ.ศ.
                $.ajax({
                    url: "<?php echo base_url('index.php/edoc/volumes'); ?>",
                    type: 'GET',
                    data: { year: year },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            var opts = '<option value="">ทุกเล่ม</option>';
                            res.data.forEach(function(v) {
                                opts += '<option value="' + v.id + '">' + (v.volume_label || v.volume_type) + '</option>';
                            });
                            $('#adv-volume').html(opts);
                        }
                    }
                });
            }
            $('#btn-advanced-search').on('click', function() {
                var $panel = $('#advanced-search-panel');
                if ($panel.hasClass('hidden')) {
                    $panel.removeClass('hidden');
                    loadAdvVolumesUser($('#adv-year').val());
                    updateAdvFilterBadgesUser();
                } else {
                    $panel.addClass('hidden');
                }
            });
            $('#btn-close-advanced-search').on('click', function() {
                $('#advanced-search-panel').addClass('hidden');
            });
            $('#adv-year').on('change', function() {
                loadAdvVolumesUser($(this).val());
            });
            $('#adv-btn-search').on('click', function() {
                table.ajax.reload();
                updateAdvFilterBadgesUser();
                updateAdvSearchButtonTextUser();
            });
            $('#adv-btn-clear').on('click', function() {
                $('#adv-doctype').val('');
                $('#adv-year').val('');
                $('#adv-volume').html('<option value="">ทุกเล่ม</option>');
                $('#adv-date-from').val('');
                $('#adv-date-to').val('');
                $('#adv-owner').val('');
                $('#adv-officeiddoc').val('');
                loadAdvVolumesUser($('#adv-year').val());
                table.ajax.reload();
                updateAdvFilterBadgesUser();
                updateAdvSearchButtonTextUser();
            });

            function getAdvancedFilterCountUser() {
                var n = 0;
                if ($('#adv-doctype').val()) n++;
                if ($('#adv-year').val()) n++;
                if ($('#adv-volume').val()) n++;
                if ($('#adv-date-from').val()) n++;
                if ($('#adv-date-to').val()) n++;
                if ($.trim($('#adv-owner').val())) n++;
                if ($.trim($('#adv-officeiddoc').val())) n++;
                return n;
            }
            function updateAdvSearchButtonTextUser() {
                var c = getAdvancedFilterCountUser();
                $('#btn-advanced-search-text').text(c > 0 ? 'ค้นหาละเอียด (' + c + ')' : 'ค้นหาละเอียด');
            }
            function updateAdvFilterBadgesUser() {
                var labels = [];
                if ($('#adv-doctype').val()) labels.push({ key: 'doctype', label: 'ประเภท: ' + $('#adv-doctype option:selected').text(), clearId: 'adv-doctype' });
                if ($('#adv-year').val()) labels.push({ key: 'year', label: 'ปี: ' + $('#adv-year').val(), clearId: 'adv-year' });
                if ($('#adv-volume').val()) labels.push({ key: 'volume', label: 'เล่ม: ' + $('#adv-volume option:selected').text(), clearId: 'adv-volume' });
                if ($('#adv-date-from').val()) labels.push({ key: 'date_from', label: 'ตั้งแต่: ' + $('#adv-date-from').val(), clearId: 'adv-date-from' });
                if ($('#adv-date-to').val()) labels.push({ key: 'date_to', label: 'ถึง: ' + $('#adv-date-to').val(), clearId: 'adv-date-to' });
                if ($.trim($('#adv-owner').val())) labels.push({ key: 'owner', label: 'เจ้าของ: ' + $('#adv-owner').val(), clearId: 'adv-owner' });
                if ($.trim($('#adv-officeiddoc').val())) labels.push({ key: 'officeiddoc', label: 'เลขที่: ' + $('#adv-officeiddoc').val(), clearId: 'adv-officeiddoc' });

                var $c = $('#advanced-search-badges');
                $c.empty();
                if (labels.length === 0) { $c.addClass('hidden'); return; }
                $c.removeClass('hidden');
                labels.forEach(function(item) {
                    var safeLabel = $('<div>').text(item.label).html();
                    $c.append($('<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 cursor-pointer hover:bg-indigo-200" data-clear-id="' + item.clearId + '">' + safeLabel + ' <i class="fas fa-times"></i></span>'));
                });
            }
            $(document).on('click', '#advanced-search-badges [data-clear-id]', function() {
                var id = $(this).data('clear-id');
                $('#' + id).val('');
                if (id === 'adv-volume') loadAdvVolumesUser($('#adv-year').val());
                table.ajax.reload();
                updateAdvFilterBadgesUser();
                updateAdvSearchButtonTextUser();
            });

            $(document).on('click', '[data-bs-dismiss="modal"]', function() {
                closeModal();
            });

        });


        // Utility functions
        function getBadgeClass(doctype) {
            const type = doctype.toLowerCase();

            // Document Types
            if (type.includes('รับภายใน') || type.includes('report')) return 'status-report';
            if (type.includes('รับภายนอก') || type.includes('memo')) return 'status-memo';
            if (type.includes('ขอความอนุเคราะห์') || type.includes('invitation')) return 'status-invitation';
            if (type.includes('เชิญ') || type.includes('plan')) return 'status-plan';
            if (type.includes('หนังสือราชการ') || type.includes('meeting')) return 'status-meeting';
            if (type.includes('ลา') || type.includes('announcement')) return 'status-announcement';
            if (type.includes('คำสั่ง') || type.includes('regulation')) return 'status-regulation';
            if (type.includes('นโยบาย') || type.includes('policy')) return 'status-policy';
            if (type.includes('ขั้นตอน') || type.includes('procedure')) return 'status-procedure';
            if (type.includes('หนังสือเวียน') || type.includes('circular')) return 'status-circular';
            if (type.includes('แจ้ง') || type.includes('notice')) return 'status-notice';
            if (type.includes('คำสั่ง') || type.includes('directive')) return 'status-directive';

            // Status Types
            if (type.includes('ด่วน') || type.includes('urgent')) return 'status-urgent';
            if (type.includes('อนุมัติ') || type.includes('approved')) return 'status-approved';
            if (type.includes('รอ') || type.includes('pending')) return 'status-pending';
            if (type.includes('ปฏิเสธ') || type.includes('rejected')) return 'status-rejected';
            if (type.includes('ร่าง') || type.includes('draft')) return 'status-draft';
            if (type.includes('เสร็จ') || type.includes('completed')) return 'status-completed';
            if (type.includes('ยกเลิก') || type.includes('cancelled')) return 'status-cancelled';
            if (type.includes('ตรวจ') || type.includes('review')) return 'status-review';

            // Access Types
            if (type.includes('ภายใน') || type.includes('internal')) return 'status-internal';
            if (type.includes('ภายนอก') || type.includes('external')) return 'status-external';
            if (type.includes('ลับ') || type.includes('confidential')) return 'status-confidential';
            if (type.includes('สาธารณะ') || type.includes('public')) return 'status-public';

            return 'status-default';
        }

        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `position-fixed top-0 end-0 m-3 alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Parse fileaddress (JSON หรือ comma-separated) — ป้องกันค่าผสมแบบ .pdf]
        function parseFileAddress(fileaddress) {
            if (!fileaddress || String(fileaddress).trim() === '') return [];
            const raw = String(fileaddress).trim();
            let list = [];
            try {
                if (raw.startsWith('[') && raw.endsWith(']')) {
                    list = JSON.parse(raw);
                } else {
                    list = raw.split(',').map(f => f.trim().replace(/^["'\[]/, '').replace(/["'\]]$/, '').trim()).filter(f => f);
                }
            } catch (e) {
                list = raw.split(',').map(f => f.trim().replace(/^["'\[]/, '').replace(/["'\]]$/, '').trim()).filter(f => f);
            }
            return list.map(f => f.replace(/^["'\s\[\]]+|["'\s\[\]]+$/g, '')).filter(f => f);
        }

        // Document info function
        function info(id) {
            // Show modal
            const modalElement = document.getElementById('modalCenter');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Show loading
            $('.pdf-container').html(`
        <div class="text-center text-muted">
            <div class="loading-spinner mb-3"></div>
            <p>กำลังโหลดข้อมูล...</p>
        </div>
    `);

            $.ajax({
                type: "POST",
                url: "<?php echo base_url('index.php/edoc/getdocinfo'); ?>",
                data: {
                    iddoc: id
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        var result_data = response.result;

                        // Populate document info
                        $('#msg_officeiddoc').text(result_data.officeiddoc || '-');
                        $('#msg_title').text(result_data.title || '-');
                        $('#msg_doctype').text(result_data.doctype || '-');
                        if (result_data.owner) {
                            var ownerLabel = (result_data.owner + '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            $('#msg_owner').html('<span class="doc-chip doc-chip-owner status-badge" title="เจ้าของเอกสาร">' + ownerLabel + '</span>');
                        } else {
                            $('#msg_owner').text('-');
                        }
                        if (result_data.participant_chips && result_data.participant_chips.length) {
                            var chips = result_data.participant_chips;
                            var chipHtml = '<div class="doc-participant-chips flex flex-wrap gap-1">';
                            chips.forEach(function(chip) {
                                var label = (chip.name || chip.email || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                var title = (chip.email && chip.email !== chip.name) ? (' title="' + (chip.email || '').replace(/"/g, '&quot;') + '"') : '';
                                var cls = chip.email === 'ทุกคน' ? 'doc-chip doc-chip-everyone' : 'doc-chip doc-chip-user';
                                chipHtml += '<span class="status-badge ' + cls + '"' + title + '>' + label + '</span>';
                            });
                            chipHtml += '</div>';
                            $('#msg_participant').html(chipHtml);
                        } else {
                            $('#msg_participant').text(result_data.participant || '-');
                        }
                        $('#msg_order').text(result_data.order || '-');
                        $('#msg_docdate').text(result_data.datedoc ? formatDateToThai(result_data.datedoc) : '-');

                        // Add LINE sharing button to existing buttons section
                        addLineShareButton(result_data);

                        const fileContainer = $('.pdf-container');

                        const fileList = parseFileAddress(result_data.fileaddress);
                        const firstFile = fileList[0] || '';

                        if (!firstFile) {
                            // No file
                            $('#msg_fileaddresslink').addClass('d-none');
                            fileContainer.html(`
                        <div class="text-center py-5">
                            <i class="fas fa-file-slash fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">ไม่พบไฟล์แนบ</h5>
                            <p class="text-muted">ไม่มีไฟล์แนบหรือไฟล์อาจสูญหาย</p>
                        </div>
                    `);
                        } else {
                            // File exists — ใช้ไฟล์แรกและลบอักขระปลายทางผิดพลาด (เช่น .pdf])
                            const basePdfUrl = "<?php echo base_url('index.php/edoc/viewPDF/'); ?>" + id + "?file=true";
                            const fileUrl = basePdfUrl + (firstFile ? "&subfile=" + encodeURIComponent(firstFile) : "");
                            const fileExtension = firstFile.split('.').pop().replace(/["'\]]+$/, '').toLowerCase();

                            $('#msg_fileaddresslink').removeClass('d-none').attr("href", fileUrl);

                            // Determine file type text
                            let fileTypeText = 'เอกสาร';
                            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
                                fileTypeText = 'รูปภาพ';
                            } else if (fileExtension === 'pdf') {
                                fileTypeText = 'PDF';
                            } else if (['doc', 'docx'].includes(fileExtension)) {
                                fileTypeText = 'Word';
                            } else if (['xls', 'xlsx'].includes(fileExtension)) {
                                fileTypeText = 'Excel';
                            }

                            $('#msg_fileaddresslink').html(`<i class="fas fa-download me-1"></i>ดาวน์โหลด ${fileTypeText}`);

                            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
                                // Image
                                fileContainer.html(`
                            <div class="text-center p-3">
                                <img src="${fileUrl}" class="img-fluid rounded shadow" 
                                     style="max-height: 500px;" alt="${result_data.title}"
                                     onload="this.style.opacity=1" 
                                     style="opacity:0; transition: opacity 0.3s;">
                            </div>
                        `);
                            } else if (fileExtension === 'pdf' && !isMobile()) {
                                // PDF on desktop
                                fileContainer.html(`
                            <embed src="${fileUrl}" width="100%" height="600" type="application/pdf" class="rounded">
                        `);
                            } else {
                                // Other files or mobile PDF
                                let iconClass = 'fa-file';
                                let iconColor = 'text-primary';

                                if (fileExtension === 'pdf') {
                                    iconClass = 'fa-file-pdf';
                                    iconColor = 'text-danger';
                                } else if (['doc', 'docx'].includes(fileExtension)) {
                                    iconClass = 'fa-file-word';
                                    iconColor = 'text-primary';
                                } else if (['xls', 'xlsx'].includes(fileExtension)) {
                                    iconClass = 'fa-file-excel';
                                    iconColor = 'text-success';
                                }

                                fileContainer.html(`
                            <div class="text-center py-5">
                                <i class="fas ${iconClass} fa-4x ${iconColor} mb-3"></i>
                                <h5 class="mb-3">${result_data.title}</h5>
                                <p class="text-muted mb-4">
                                    ${isMobile() ? 
                                        'ไม่สามารถแสดงตัวอย่างไฟล์นี้ได้บนอุปกรณ์มือถือ' : 
                                        'ไม่สามารถแสดงตัวอย่างไฟล์นี้ได้โดยตรง'} 
                                    กรุณาดาวน์โหลดเพื่อดู
                                </p>
                                <a href="${fileUrl}" download class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i>
                                    ดาวน์โหลด ${fileTypeText}
                                </a>
                            </div>
                        `);
                            }

                            // Download handler
                            $('#msg_fileaddresslink').off('click').on('click', function(e) {
                                e.preventDefault();
                                downloadFile(fileUrl, result_data.title + '.' + fileExtension);
                            });
                        }
                    } else {
                        showNotification(response.message || 'ไม่สามารถโหลดข้อมูลเอกสารได้', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseJSON || error);
                    showNotification("เกิดข้อผิดพลาดในการโหลดข้อมูล กรุณาลองใหม่อีกครั้ง", 'error');
                }
            });
        }

        // Simple function to add LINE share button
        function addLineShareButton(documentData) {
            // Find the buttons container in the modal
            const buttonsContainer = document.querySelector('#modalCenter .modal-body .d-flex.gap-2');

            // Remove existing LINE button if it exists
            const existingLineBtn = document.getElementById('lineShareBtn');
            if (existingLineBtn) {
                existingLineBtn.remove();
            }

            // Create LINE share button
            const lineButton = document.createElement('button');
            lineButton.id = 'lineShareBtn';
            lineButton.className = 'btn btn-success btn-sm';
            lineButton.innerHTML = '<i class="fab fa-line me-1"></i>แชร์ LINE';
            lineButton.onclick = () => shareToLine(documentData);

            // Add to buttons container
            if (buttonsContainer) {
                buttonsContainer.appendChild(lineButton);
            } else {
                // If no buttons container exists, create one
                const fileSection = document.querySelector('#modalCenter .border-top.pt-4');
                const headerDiv = fileSection.querySelector('.d-flex.justify-content-between');
                if (headerDiv && headerDiv.querySelector('.d-flex.gap-2')) {
                    headerDiv.querySelector('.d-flex.gap-2').appendChild(lineButton);
                }
            }
        }

        // Simple LINE sharing function
        function shareToLine(documentData) {
            if (!documentData.fileaddress) {
                // No file - share text only
                shareTextToLine(documentData);
                return;
            }

            // Check if Web Share API is supported (for file sharing)
            if (navigator.share && isMobile()) {
                shareFileToLine(documentData);
            } else {
                // Fallback to text with download link
                shareTextWithDownloadToLine(documentData);
            }
        }

        // Share actual file using Web Share API (mobile only)
        function shareFileToLine(documentData) {
            const fileList = parseFileAddress(documentData.fileaddress);
            const firstFile = fileList[0] || '';
            const ext = firstFile ? firstFile.split('.').pop().replace(/["'\]]+$/, '') : 'pdf';
            const fileUrl = "<?php echo base_url('index.php/edoc/viewPDF/'); ?>" + documentData.iddoc + "?file=true" + (firstFile ? "&subfile=" + encodeURIComponent(firstFile) : "");
            const fileName = documentData.title + '.' + ext;

            showNotification('กำลังเตรียมไฟล์...', 'info');

            // Download file and share
            fetch(fileUrl)
                .then(response => {
                    if (!response.ok) throw new Error('ไม่สามารถดาวน์โหลดไฟล์ได้');
                    return response.blob();
                })
                .then(blob => {
                    const file = new File([blob], fileName, {
                        type: blob.type
                    });

                    const shareData = {
                        title: documentData.title,
                        text: `📄 ${documentData.title}\n📋 เลขที่: ${documentData.officeiddoc || '-'}\n📅 วันที่: ${documentData.datedoc || '-'}`,
                        files: [file]
                    };

                    if (navigator.canShare && navigator.canShare(shareData)) {
                        return navigator.share(shareData);
                    } else {
                        throw new Error('ไม่สามารถแชร์ไฟล์ได้');
                    }
                })
                .then(() => {
                    showNotification('แชร์ไฟล์สำเร็จ!', 'success');
                })
                .catch(error => {
                    console.error('File share error:', error);
                    showNotification('ไม่สามารถแชร์ไฟล์ได้ กำลังแชร์ข้อความแทน...', 'warning');
                    shareTextWithDownloadToLine(documentData);
                });
        }

        // Share text with download link
        function shareTextWithDownloadToLine(documentData) {
            const fileUrl = "<?php echo base_url('index.php/edoc/viewPDF/'); ?>" + documentData.iddoc + "?file=true";

            const shareMessage = `📄 ${documentData.title}

📋 เลขที่หนังสือ: ${documentData.officeiddoc || '-'}
📅 วันที่: ${documentData.datedoc || '-'}
🏢 เจ้าของเอกสาร: ${documentData.owner || '-'}
📝 ประเภท: ${documentData.doctype || '-'}

📎 ดาวน์โหลดไฟล์: ${fileUrl}`;

            openLineWithMessage(shareMessage);
        }

        // Share text only (when no file)
        function shareTextToLine(documentData) {
            const shareMessage = `📄 ${documentData.title}

📋 เลขที่หนังสือ: ${documentData.officeiddoc || '-'}
📅 วันที่: ${documentData.datedoc || '-'}
🏢 เจ้าของเอกสาร: ${documentData.owner || '-'}
📝 ประเภท: ${documentData.doctype || '-'}

ℹ️ ไม่มีไฟล์แนบ`;

            openLineWithMessage(shareMessage);
        }

        // Open LINE with message
        function openLineWithMessage(message) {
            const lineUrl = `https://line.me/R/msg/text/?${encodeURIComponent(message)}`;

            if (isMobile()) {
                window.location.href = lineUrl;
            } else {
                window.open(lineUrl, '_blank', 'width=500,height=600');
            }

            showNotification('เปิด LINE เพื่อแชร์เอกสาร', 'success');
        }



        // View statistics function


        function downloadFile(url, filename) {
            if (isMobile()) {
                window.open(url, '_blank');
            } else {
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = filename || 'document';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(blobUrl);
                        a.remove();
                        showNotification('ดาวน์โหลดสำเร็จ', 'success');
                    })
                    .catch(error => {
                        console.error('Download error:', error);
                        showNotification('เกิดข้อผิดพลาดในการดาวน์โหลด กรุณาลองใหม่อีกครั้ง', 'error');
                        window.open(url, '_blank');
                    });
            }
        }

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Escape key to close modals
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }

            // Ctrl+F for global search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                $('#globalSearch').focus();
            }
        });

        // Auto-refresh table every 5 minutes
        setInterval(function() {
            if (table) {
                table.ajax.reload(null, false);
            }
        }, 300000); // 5 minutes

        // Show loading overlay for long operations
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        // Initialize tooltips
        $(function() {
            $('[title]').tooltip();
        });

        // Add loading indicator for AJAX requests
        $(document).ajaxStart(function() {
            showLoading();
        }).ajaxStop(function() {
            hideLoading();
        });



        function formatDateToThai(dateString) {
            if (!dateString) return '-';

            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString; // Return original if invalid

                const thaiMonths = [
                    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ];

                const day = date.getDate();
                const month = thaiMonths[date.getMonth()];
                const year = date.getFullYear() + 543; // Convert to Buddhist Era

                return `${day} ${month} ${year}`;
            } catch (error) {
                return dateString; // Return original if error
            }
        }
    </script>
</body>

</html>