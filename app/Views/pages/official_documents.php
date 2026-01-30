<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title"><?= esc($page_title) ?></h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>คำสั่ง/ประกาศ/ระเบียบ</span>
        </div>
    </div>
</section>

<!-- Documents Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-header__title">เอกสารเผยแพร่</h2>
            <p class="section-header__description">รวมคำสั่ง ประกาศ และระเบียบต่างๆ ของคณะวิทยาศาสตร์และเทคโนโลยี</p>
        </div>
        
        <div class="documents-grid">
            <!-- Category: คำสั่ง -->
            <div class="card animate-on-scroll">
                <div class="card__header">
                    <div class="card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <h3 class="card__title">คำสั่ง</h3>
                </div>
                <div class="document-list">
                    <a href="https://sci.uru.ac.th/doctopic/250" target="_blank" class="document-item">
                        <div class="document-item__icon type-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">คำสั่งแต่งตั้งอาจารย์ที่ปรึกษานักศึกษาภาคปกติ ปีการศึกษา 2567</span>
                            <span class="document-item__type">Link</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/doctopic/200" target="_blank" class="document-item">
                        <div class="document-item__icon type-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">คำสั่งแต่งตั้งอาจารย์ที่ปรึกษานักศึกษาภาคปกติ ปีการศึกษา 2566</span>
                            <span class="document-item__type">Link</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/docs/download/teacher2565.pdf" target="_blank" class="document-item">
                        <div class="document-item__icon type-pdf">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15l3 3 3-3"/><path d="M12 18V12"/></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">คำสั่งแต่งตั้งอาจารย์ที่ปรึกษานักศึกษาภาคปกติ ปีการศึกษา 2565</span>
                            <span class="document-item__type">PDF</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Category: แผนพัฒนาและระเบียบ -->
            <div class="card animate-on-scroll">
                <div class="card__header">
                    <div class="card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <h3 class="card__title">แผนพัฒนาและระเบียบ</h3>
                </div>
                <div class="document-list">
                    <a href="https://sci.uru.ac.th/doctopic/222" target="_blank" class="document-item">
                        <div class="document-item__icon type-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">แผนพัฒนาบุคลากรระยะ 5 ปี (ปีงบประมาณ 2566-2569)</span>
                            <span class="document-item__type">Link</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/docs/download/6016.pdf" target="_blank" class="document-item">
                        <div class="document-item__icon type-pdf">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15l3 3 3-3"/><path d="M12 18V12"/></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">หลักเกณฑ์ อัตราค่าใช้จ่าย และแนวทางการพิจารณางบประมาณ พ.ศ. 2561</span>
                            <span class="document-item__type">PDF</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/docs/download/6015.pdf" target="_blank" class="document-item">
                        <div class="document-item__icon type-pdf">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15l3 3 3-3"/><path d="M12 18V12"/></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">หลักเกณฑ์และอัตราค่าใช้จ่ายในลักษณะค่าตอบแทน พ.ศ. 2561</span>
                            <span class="document-item__type">PDF</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/docs/download/6014.pdf" target="_blank" class="document-item">
                        <div class="document-item__icon type-pdf">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15l3 3 3-3"/><path d="M12 18V12"/></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">ระเบียบกระทรวงการคลังว่าด้วยค่าใช้จ่ายในการฝึกอบรม</span>
                            <span class="document-item__type">PDF</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Category: สำหรับนักศึกษา -->
            <div class="card animate-on-scroll">
                <div class="card__header">
                    <div class="card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                    </div>
                    <h3 class="card__title">สำหรับนักศึกษา</h3>
                </div>
                <div class="document-list">
                    <a href="https://sci.uru.ac.th/doctopic/253" target="_blank" class="document-item">
                        <div class="document-item__icon type-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">ปฏิทินกิจกรรมนักศึกษา ปีการศึกษา 2568</span>
                            <span class="document-item__type">Link</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/doctopic/252" target="_blank" class="document-item">
                        <div class="document-item__icon type-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">คู่มือผู้ปกครองคณะวิทยาศาสตร์และเทคโนโลยี</span>
                            <span class="document-item__type">Link</span>
                        </div>
                    </a>
                    <a href="https://sci.uru.ac.th/doctopic/205" target="_blank" class="document-item">
                        <div class="document-item__icon type-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name">แนวปฏิบัติการแต่งกายนักศึกษา</span>
                            <span class="document-item__type">Link</span>
                        </div>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</section>

<style>
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.card__header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #edf2f7);
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card__icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card__icon svg {
    width: 24px;
    height: 24px;
}

.card__title {
    margin: 0;
    font-size: 1.25rem;
    color: var(--text-primary);
}

.document-list {
    padding: 1rem;
    flex: 1;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.2s ease;
    border: 1px solid transparent;
    margin-bottom: 0.5rem;
}

.document-item:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    transform: translateX(4px);
}

.document-item__icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.document-item__icon svg {
    width: 20px;
    height: 20px;
}

.document-item__icon.type-pdf {
    background: #fee2e2;
    color: #dc2626;
}

.document-item__icon.type-doc,
.document-item__icon.type-docx {
    background: #dbeafe;
    color: #2563eb;
}

.document-item__icon.type-link {
    background: #f3f4f6;
    color: #4b5563;
}

.document-item__content {
    flex: 1;
    overflow: hidden;
}

.document-item__name {
    display: block;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-item__type {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.125rem;
}

@media (max-width: 640px) {
    .documents-grid {
        grid-template-columns: 1fr;
    }
    
    .card__header {
        padding: 1.25rem;
    }
}
</style>

<?= $this->endSection() ?>
