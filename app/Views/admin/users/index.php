<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2>จัดการผู้ใช้</h2>
            <?php if (!empty($users)): ?>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">ทั้งหมด <?= count($users) ?> รายการ (จากตาราง user ใน newScience)</p>
            <?php endif; ?>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="text" id="userSearch" placeholder="ค้นหาชื่อ, อีเมล, login_uid..." class="form-input" style="width: 280px;">
            <button type="button" id="clearSearch" class="btn btn-outline btn-sm" style="display: none;">ล้าง</button>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($users)): ?>
            <div class="empty-state empty-state--news">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                </svg>
                <h3>ยังไม่มีผู้ใช้</h3>
                <p>ไม่มีข้อมูลผู้ใช้ในระบบ</p>
            </div>
        <?php else: ?>
            <div class="news-table-wrap">
                <table class="table" role="table" aria-label="รายการผู้ใช้จากตาราง user">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 60px;">ลำดับ</th>
                            <th scope="col">ชื่อ-นามสกุล</th>
                            <th scope="col">อีเมล</th>
                            <th scope="col" style="width: 100px;">login_uid</th>
                            <th scope="col" style="width: 100px;">บทบาท</th>
                            <th scope="col" style="width: 90px;">สถานะ</th>
                            <th scope="col" style="width: 140px;">วันที่สมัคร</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td style="font-variant-numeric: tabular-nums;"><?= $i + 1 ?></td>
                                <td class="news-title-cell">
                                    <span style="font-weight: 500;"><?= esc($u['display_name'] ?: '—') ?></span>
                                </td>
                                <td>
                                    <span style="font-size: 0.875rem;"><?= esc($u['email']) ?></span>
                                </td>
                                <td>
                                    <span style="font-size: 0.875rem; color: var(--color-gray-600);"><?= esc($u['login_uid'] ?: '—') ?></span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: var(--color-gray-200); color: var(--color-gray-700);"><?= esc($u['role']) ?></span>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge badge-success">ใช้งาน</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: var(--color-gray-200); color: var(--color-gray-600);">ไม่ใช้งาน</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.875rem; color: var(--color-gray-600); font-variant-numeric: tabular-nums;">
                                    <?= $u['created_at'] ? date('d/m/Y H:i', strtotime($u['created_at'])) : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded - Initializing user search');

        const searchInput = document.getElementById('userSearch');
        const clearBtn = document.getElementById('clearSearch');
        const tableRows = document.querySelectorAll('tbody tr');
        const countHint = document.querySelector('.form-hint');

        console.log('Elements found:', {
            searchInput: !!searchInput,
            clearBtn: !!clearBtn,
            tableRows: tableRows.length,
            countHint: !!countHint
        });

        if (!searchInput) {
            console.error('Search input not found');
            return;
        }

        const totalCount = tableRows.length;

        function filterUsers() {
            const keyword = searchInput.value.toLowerCase().trim();
            console.log('Filtering with keyword:', keyword);
            let visibleCount = 0;

            tableRows.forEach(function(row) {
                const nameCell = row.querySelector('td:nth-child(2) span');
                const emailCell = row.querySelector('td:nth-child(3) span');
                const loginUidCell = row.querySelector('td:nth-child(4) span');

                if (!nameCell || !emailCell || !loginUidCell) {
                    console.log('Missing cell in row:', row);
                    return;
                }

                const displayName = nameCell.textContent.toLowerCase();
                const email = emailCell.textContent.toLowerCase();
                const loginUid = loginUidCell.textContent.toLowerCase();

                if (displayName.includes(keyword) || email.includes(keyword) || loginUid.includes(keyword)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            console.log('Visible count:', visibleCount, 'of', totalCount);

            // Update count display
            if (countHint) {
                if (keyword) {
                    countHint.textContent = 'แสดง ' + visibleCount + ' จาก ' + totalCount + ' รายการ';
                } else {
                    countHint.textContent = 'ทั้งหมด ' + totalCount + ' รายการ (จากตาราง user ใน newScience)';
                }
            }

            // Show/hide clear button
            if (clearBtn) {
                clearBtn.style.display = keyword ? 'inline-flex' : 'none';
            }
        }

        // Auto filter on input
        searchInput.addEventListener('input', filterUsers);
        console.log('Event listener added to search input');

        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterUsers();
                searchInput.focus();
            });
        }

        // Focus on search input
        searchInput.focus();
        console.log('User search initialized successfully');
    });
</script>
<?= $this->endSection() ?>