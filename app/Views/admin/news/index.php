<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2>จัดการข่าวสาร</h2>
            <?php if (!empty($news)): ?>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">แสดง <?= count($news) ?> รายการ</p>
            <?php endif; ?>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <!-- Search Form -->
            <form action="<?= base_url('admin/news') ?>" method="get" style="display: flex; align-items: center; gap: 0.5rem; background: #f8f9fa; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #e9ecef;">
                <div style="position: relative;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="keyword" placeholder="ค้นหาหัวข้อข่าว..." value="<?= esc($keyword ?? '') ?>"
                        style="width: 220px; padding: 0.5rem 0.75rem 0.5rem 2.25rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.875rem; outline: none; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='#4dabf7'; this.style.boxShadow='0 0 0 3px rgba(77, 171, 247, 0.1)';"
                        onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';">
                </div>

                <?php if (!empty($tags)): ?>
                    <select name="tag_id"
                        style="width: 140px; padding: 0.5rem 0.75rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.875rem; background: white; cursor: pointer; outline: none;"
                        onfocus="this.style.borderColor='#4dabf7';"
                        onblur="this.style.borderColor='#ced4da';">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>" <?= ($selected_tag ?? '') == $tag['id'] ? 'selected' : '' ?>>
                                <?= esc($tag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button type="submit"
                    style="display: flex; align-items: center; gap: 0.375rem; padding: 0.5rem 1rem; background: #339af0; color: white; border: none; border-radius: 6px; font-size: 0.875rem; cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.background='#228be6';"
                    onmouseout="this.style.background='#339af0';">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    ค้นหา
                </button>

                <?php if (!empty($keyword) || !empty($selected_tag)): ?>
                    <a href="<?= base_url('admin/news') ?>"
                        style="display: flex; align-items: center; padding: 0.5rem 0.75rem; color: #6c757d; text-decoration: none; border-radius: 6px; font-size: 0.875rem; transition: background 0.2s;"
                        onmouseover="this.style.background='#e9ecef';"
                        onmouseout="this.style.background='transparent';">
                        ล้าง
                    </a>
                <?php endif; ?>
            </form>

            <?php if (!empty($news)): ?>
                <?php
                $published = array_filter($news, fn($n) => ($n['status'] ?? '') === 'published');
                $drafts = array_filter($news, fn($n) => ($n['status'] ?? '') === 'draft');
                ?>
                <span class="news-stat-pill">
                    <strong><?= count($published) ?></strong> เผยแพร่
                </span>
                <span class="news-stat-pill">
                    <strong><?= count($drafts) ?></strong> ร่าง
                </span>
            <?php endif; ?>
            <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                เพิ่มข่าว
            </a>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <!-- Loading State -->
        <div id="newsLoading" style="display: none; padding: 3rem; text-align: center;">
            <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #339af0; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 1rem; color: #6c757d;">กำลังโหลด...</p>
        </div>

        <!-- Empty State -->
        <div id="newsEmpty" class="empty-state empty-state--news" style="display: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <h3>ยังไม่มีข่าว</h3>
            <p>เพิ่มข่าวสารฉบับแรกเพื่อแสดงบนเว็บไซต์</p>
            <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary">สร้างข่าว</a>
        </div>

        <!-- News Table -->
        <div id="newsTableContainer" class="news-table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 90px;">ภาพ</th>
                        <th>หัวข้อ</th>
                        <th style="width: 120px;">Tag</th>
                        <th style="width: 120px;">ผู้เขียน</th>
                        <th style="width: 90px;">สถานะ</th>
                        <th style="width: 90px;">กิจกรรม</th>
                        <th style="width: 100px;">วันที่</th>
                        <th style="width: 160px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="newsTableBody">
                    <!-- AJAX loaded content -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="newsPagination" style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center;">
            <span id="paginationInfo" style="color: #6c757d; font-size: 0.875rem;"></span>
            <div id="paginationControls" style="display: flex; gap: 0.5rem;"></div>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .pagination-btn {
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
        background: white;
        color: #495057;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .pagination-btn:hover:not(:disabled) {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-btn.active {
        background: #339af0;
        border-color: #339af0;
        color: white;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Tag badge styles function
    function getTagBadgeStyle(slug) {
        const styles = {
            'general': 'background: #17a2b8; color: white;',
            'student_activity': 'background: #28a745; color: white;',
            'research_grant': 'background: #ffc107; color: #000;',
            'announcement': 'background: #dc3545; color: white;',
            'news': 'background: #007bff; color: white;',
            'event': 'background: #fd7e14; color: white;'
        };

        // Program colors
        if (slug.includes('math') || slug === 'applied_mathematics') {
            return 'background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9;';
        } else if (slug.includes('biology')) {
            return 'background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;';
        } else if (slug.includes('chemistry')) {
            return 'background: #fff3e0; color: #ef6c00; border: 1px solid #ffcc80;';
        } else if (slug.includes('computer') || slug.includes('it')) {
            return 'background: #f3e5f5; color: #6a1b9a; border: 1px solid #ce93d8;';
        } else if (slug.includes('data')) {
            return 'background: #e0f7fa; color: #00838f; border: 1px solid #80deea;';
        } else if (slug.includes('environment')) {
            return 'background: #e8f5e9; color: #1b5e20; border: 1px solid #81c784;';
        } else if (slug.includes('food') || slug.includes('nutrition')) {
            return 'background: #fff8e1; color: #f57f17; border: 1px solid #ffd54f;';
        } else if (slug.includes('physics')) {
            return 'background: #eceff1; color: #37474f; border: 1px solid #90a4ae;';
        } else if (slug.includes('public_health')) {
            return 'background: #fce4ec; color: #c2185b; border: 1px solid #f48fb1;';
        } else if (slug.includes('biotech')) {
            return 'background: #f1f8e9; color: #558b2f; border: 1px solid #aed581;';
        } else if (slug.includes('tourism')) {
            return 'background: #e1f5fe; color: #0277bd; border: 1px solid #81d4fa;';
        } else if (slug.includes('applied_sci')) {
            return 'background: #ede7f6; color: #4527a0; border: 1px solid #b39ddb;';
        }

        return styles[slug] || 'background: #6c757d; color: white;';
    }

    // Render tag badges
    function renderTags(tags) {
        if (!tags || tags.length === 0) {
            return '<span style="color: var(--color-gray-400); font-size: 0.875rem;">—</span>';
        }

        let html = '<div style="display: flex; flex-wrap: wrap; gap: 4px;">';
        tags.forEach(tag => {
            const style = getTagBadgeStyle(tag.slug || '');
            html += `<span style="padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; white-space: nowrap; ${style}">${tag.name || tag.slug}</span>`;
        });
        html += '</div>';
        return html;
    }

    // Render news table row
    function renderNewsRow(article) {
        const baseUrl = '<?= base_url() ?>';
        const featuredImage = article.featured_image ?
            `<img src="${baseUrl}serve/thumb/news/${article.featured_image.split('/').pop()}" alt="" class="news-thumb" width="72" height="48">` :
            '<div class="news-thumb-placeholder">ไม่มีภาพ</div>';

        const excerpt = article.excerpt ?
            `<p class="news-excerpt">${article.excerpt.substring(0, 80)}…</p>` :
            '';

        const statusBadge = article.status === 'published' ?
            '<span class="badge badge-success">เผยแพร่</span>' :
            '<span class="badge badge-warning">ร่าง</span>';

        const eventBadge = article.display_as_event ?
            '<span class="badge badge-success" title="แสดงใน section กิจกรรมที่จะมาถึง">Event</span>' :
            '<span style="color: var(--color-gray-400); font-size: 0.875rem;">—</span>';

        const authorName = (article.gf_name || '') + ' ' + (article.gl_name || '');
        const author = authorName.trim() || '—';

        const date = article.created_at_formatted || new Date(article.created_at).toLocaleDateString('th-TH');

        return `
        <tr>
            <td>${featuredImage}</td>
            <td class="news-title-cell">
                <a href="${baseUrl}admin/news/edit/${article.id}" class="news-title-link">${article.title}</a>
                ${excerpt}
            </td>
            <td>${renderTags(article.tags)}</td>
            <td><span style="font-size: 0.875rem; color: var(--color-gray-600);">${author}</span></td>
            <td>${statusBadge}</td>
            <td>${eventBadge}</td>
            <td style="font-size: 0.875rem; color: var(--color-gray-600);">${date}</td>
            <td class="news-actions-cell">
                <div class="actions">
                    <a href="${baseUrl}admin/news/edit/${article.id}" class="btn btn-secondary btn-sm">แก้ไข</a>
                    <a href="${baseUrl}admin/news/delete/${article.id}" class="btn btn-danger btn-sm" onclick="return confirm('ต้องการลบข่าวนี้หรือไม่?')">ลบ</a>
                </div>
            </td>
        </tr>
    `;
    }

    // Load news via AJAX
    let currentPage = 1;
    let currentKeyword = '<?= esc($keyword ?? '') ?>';
    let currentTagId = '<?= esc($selected_tag ?? '') ?>';

    function loadNews(page = 1) {
        currentPage = page;

        // Show loading
        document.getElementById('newsLoading').style.display = 'block';
        document.getElementById('newsTableContainer').style.display = 'none';
        document.getElementById('newsEmpty').style.display = 'none';
        document.getElementById('newsPagination').style.display = 'none';

        // Build URL
        const params = new URLSearchParams();
        params.append('page', page);
        if (currentKeyword) params.append('keyword', currentKeyword);
        if (currentTagId) params.append('tag_id', currentTagId);

        fetch(`${baseUrl}admin/news/get-paginated?${params.toString()}`)
            .then(response => response.json())
            .then(result => {
                document.getElementById('newsLoading').style.display = 'none';

                if (result.data.length === 0) {
                    document.getElementById('newsEmpty').style.display = 'block';
                    document.getElementById('newsTableContainer').style.display = 'none';
                    document.getElementById('newsPagination').style.display = 'none';
                } else {
                    document.getElementById('newsEmpty').style.display = 'none';
                    document.getElementById('newsTableContainer').style.display = 'block';
                    document.getElementById('newsPagination').style.display = 'flex';

                    // Render table
                    const tbody = document.getElementById('newsTableBody');
                    tbody.innerHTML = result.data.map(article => renderNewsRow(article)).join('');

                    // Update pagination info
                    const start = (result.page - 1) * result.perPage + 1;
                    const end = Math.min(start + result.data.length - 1, result.total);
                    document.getElementById('paginationInfo').textContent =
                        `แสดง ${start}-${end} จาก ${result.total} รายการ`;

                    // Render pagination controls
                    renderPagination(result.page, result.totalPages, result.total);
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
                document.getElementById('newsLoading').style.display = 'none';
                document.getElementById('newsEmpty').style.display = 'block';
                document.getElementById('newsEmpty').innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h3>เกิดข้อผิดพลาด</h3>
                <p>ไม่สามารถโหลดข้อมูลได้</p>
            `;
            });
    }

    // Render pagination controls
    function renderPagination(currentPage, totalPages, total) {
        const container = document.getElementById('paginationControls');
        let html = '';

        // Previous button
        html += `<button class="pagination-btn" onclick="loadNews(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>← ก่อนหน้า</button>`;

        // Page numbers
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);

        if (endPage - startPage < maxButtons - 1) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        if (startPage > 1) {
            html += `<button class="pagination-btn" onclick="loadNews(1)">1</button>`;
            if (startPage > 2) html += `<span style="padding: 0.375rem;">…</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPage ? 'active' : '';
            html += `<button class="pagination-btn ${active}" onclick="loadNews(${i})">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span style="padding: 0.375rem;">…</span>`;
            html += `<button class="pagination-btn" onclick="loadNews(${totalPages})">${totalPages}</button>`;
        }

        // Next button
        html += `<button class="pagination-btn" onclick="loadNews(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>ถัดไป →</button>`;

        container.innerHTML = html;
    }

    // Handle search form submit
    document.querySelector('form[action="<?= base_url('admin/news') ?>"]').addEventListener('submit', function(e) {
        e.preventDefault();
        currentKeyword = this.querySelector('input[name="keyword"]').value;
        currentTagId = this.querySelector('select[name="tag_id"]')?.value || '';
        loadNews(1);
    });

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadNews(1);
    });
</script>
<?= $this->endSection() ?>