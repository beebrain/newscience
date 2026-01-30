<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
    <div class="section-header">
        <span class="section-header__subtitle">เอกสารเผยแพร่</span>
        <h2 class="section-header__title">เอกสารภายในมหาวิทยาลัย</h2>
        <p class="section-header__description">รวมประกาศ คำสั่ง และระเบียบต่างๆ ของมหาวิทยาลัยราชภัฏอุตรดิตถ์</p>
    </div>

    <!-- Internal University Documents Content -->
    <div class="internal-docs-section animate-on-scroll">
        <ul class="internal-docs-list">
            <li class="internal-docs-list__item">
                <a href="#" class="internal-docs-list__link">
                    <span class="internal-docs-list__bullet"></span>
                    <span>ประกาศมหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
                </a>
            </li>
            <li class="internal-docs-list__item">
                <a href="#" class="internal-docs-list__link">
                    <span class="internal-docs-list__bullet"></span>
                    <span>ข้อบังคับมหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
                </a>
            </li>
            <li class="internal-docs-list__item">
                <a href="#" class="internal-docs-list__link">
                    <span class="internal-docs-list__bullet"></span>
                    <span>ระเบียบมหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
                </a>
            </li>
            <li class="internal-docs-list__item">
                <a href="#" class="internal-docs-list__link">
                    <span class="internal-docs-list__bullet"></span>
                    <span>คำสั่งสภามหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
                </a>
            </li>
            <li class="internal-docs-list__item">
                <a href="#" class="internal-docs-list__link">
                    <span class="internal-docs-list__bullet"></span>
                    <span>มติมหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
                </a>
            </li>
            <li class="internal-docs-list__item">
                <a href="#" class="internal-docs-list__link">
                    <span class="internal-docs-list__bullet"></span>
                    <span>รายงานการประชุมสภามหาวิทยาลัย</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* Internal Documents Section Styles */
    .internal-docs-section {
        background: #fff;
        padding: 0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        max-width: 800px;
        margin: 0 auto;
    }

    .internal-docs-section:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .internal-docs-list {
        list-style: none;
        padding: 1.5rem;
        margin: 0;
    }

    .internal-docs-list__item {
        margin-bottom: 0.75rem;
    }

    .internal-docs-list__item:last-child {
        margin-bottom: 0;
    }

    .internal-docs-list__link {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        padding: 1rem;
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-color);
        background: var(--color-gray-200);
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .internal-docs-list__link:hover {
        background: var(--color-gray-100);
        border-color: var(--color-primary);
        color: var(--color-primary);
        transform: translateX(4px);
    }

    .internal-docs-list__bullet {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: var(--color-gray-700);
        border-radius: 50%;
        margin-top: 0.5rem;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .internal-docs-list__link:hover .internal-docs-list__bullet {
        background: var(--color-primary);
        transform: scale(1.3);
    }

    .internal-docs-list__link span:last-child {
        flex: 1;
        line-height: 1.6;
    }
</style>

<?= $this->endSection() ?>
