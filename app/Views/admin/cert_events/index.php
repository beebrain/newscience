<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">จัดการกิจกรรมใบรับรอง</h2>
        <button type="button" class="btn btn-primary" onclick="openModal('certEventModal')">
            + สร้างกิจกรรมใหม่
        </button>
    </div>

    <div class="card-body">
        <!-- Filter -->
        <div style="margin-bottom: 1rem;">
            <form method="get" style="display: flex; gap: 0.5rem; align-items: center;">
                <label>สถานะ:</label>
                <select name="status" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    <option value="">ทั้งหมด</option>
                    <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>ร่าง</option>
                    <option value="open" <?= $filter_status === 'open' ? 'selected' : '' ?>>เปิด</option>
                    <option value="issued" <?= $filter_status === 'issued' ? 'selected' : '' ?>>ออก Cert แล้ว</option>
                    <option value="closed" <?= $filter_status === 'closed' ? 'selected' : '' ?>>ปิด</option>
                </select>
            </form>
        </div>

        <?php if (empty($events)): ?>
            <div class="alert alert-info">ยังไม่มีกิจกรรม</div>
        <?php else: ?>
            <table class="table" style="width: 100%; border-collapse: collapse;" id="eventsTable">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 0.75rem; text-align: left;">กิจกรรม</th>
                        <th style="padding: 0.75rem; text-align: center;">วันที่</th>
                        <th style="padding: 0.75rem; text-align: center;">สถานะ</th>
                        <th style="padding: 0.75rem; text-align: center;">ผู้รับ</th>
                        <th style="padding: 0.75rem; text-align: center;">ออก Cert แล้ว</th>
                        <th style="padding: 0.75rem; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;" data-event-id="<?= $event['id'] ?>">
                            <td style="padding: 0.75rem;">
                                <strong><?= esc($event['title']) ?></strong>
                                <br>
                                <small style="color: #666;"><?= esc($event['template_name'] ?? '-') ?></small>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <?= $event['event_date'] ? date('d/m/Y', strtotime($event['event_date'])) : '-' ?>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <span class="badge <?= getStatusBadgeClass($event['status']) ?>">
                                    <?= getStatusLabel($event['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <?= $event['total_recipients'] ?? 0 ?>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <?= $event['issued_count'] ?? 0 ?> / <?= $event['total_recipients'] ?? 0 ?>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <div class="action-buttons" style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <a href="<?= base_url('admin/cert-events/' . $event['id']) ?>" class="btn btn-sm btn-primary">ดู</a>
                                    <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        onclick="editEvent(<?= $event['id'] ?>)">
                                        แก้ไข
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="confirmDelete(<?= $event['id'] ?>, '<?= esc($event['title'], 'js') ?>')">
                                        ลบ
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<?= view('admin/components/modal_base', [
    'modal_id' => 'certEventModal',
    'title' => 'สร้างกิจกรรมใหม่',
    'size' => 'lg',
    'content' => view('admin/cert_events/_form', [
        'event' => null,
        'templates' => $templates ?? [],
        'signers' => $signers ?? []
    ]),
    'footer' => '
        <button type="button" class="btn btn-secondary" onclick="closeModal(\'certEventModal\')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary" form="certEventForm">บันทึก</button>
    '
]) ?>

<!-- Delete Confirmation Modal -->
<?= view('admin/components/modal_base', [
    'modal_id' => 'deleteConfirmModal',
    'title' => 'ยืนยันการลบ',
    'size' => 'sm',
    'content' => '<p id="deleteConfirmText">คุณแน่ใจหรือไม่ที่จะลบกิจกรรมนี้?</p>',
    'footer' => '
        <button type="button" class="btn btn-secondary" onclick="closeModal(\'deleteConfirmModal\')">ยกเลิก</button>
        <form id="deleteForm" method="post" data-ajax="true" data-reload="true" style="display: inline;">
            ' . csrf_field() . '
            <button type="submit" class="btn btn-danger">ยืนยันลบ</button>
        </form>
    '
]) ?>

<?php
function getStatusBadgeClass($status): string
{
    return match ($status) {
        'draft' => 'badge-secondary',
        'open' => 'badge-success',
        'issued' => 'badge-primary',
        'closed' => 'badge-warning',
        default => 'badge-secondary'
    };
}

function getStatusLabel($status): string
{
    return match ($status) {
        'draft' => 'ร่าง',
        'open' => 'เปิด',
        'issued' => 'ออก Cert แล้ว',
        'closed' => 'ปิด',
        default => $status
    };
}
?>

<script src="<?= base_url('js/ajax-form-handler.js') ?>"></script>
<script>
    // Edit event - load data and open modal
    async function editEvent(eventId) {
        try {
            const response = await fetch(`<?= base_url('admin/cert-events/') ?>${eventId}/edit?ajax=1`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                // Update modal title
                document.querySelector('#certEventModal .modal-title').textContent = 'แก้ไขกิจกรรม';

                // Update form action
                const form = document.getElementById('certEventForm');
                form.action = `<?= base_url('admin/cert-events/') ?>${eventId}/update`;

                // Populate form fields
                form.querySelector('[name="title"]').value = data.event.title || '';
                form.querySelector('[name="description"]').value = data.event.description || '';
                form.querySelector('[name="event_date"]').value = data.event.event_date || '';
                form.querySelector('[name="status"]').value = data.event.status || 'draft';
                form.querySelector('[name="template_id"]').value = data.event.template_id || '';
                form.querySelector('[name="signer_id"]').value = data.event.signer_id || '';

                openModal('certEventModal');
            } else {
                AjaxForm.showToast(data.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            AjaxForm.showToast('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        }
    }

    // Confirm delete
    function confirmDelete(eventId, eventTitle) {
        document.getElementById('deleteConfirmText').textContent = `คุณแน่ใจหรือไม่ที่จะลบกิจกรรม "${eventTitle}"?`;

        const form = document.getElementById('deleteForm');
        form.action = `<?= base_url('admin/cert-events/') ?>${eventId}/delete`;

        openModal('deleteConfirmModal');
    }

    // Reset modal when closing
    document.getElementById('certEventModal')?.addEventListener('modal:close', function() {
        const form = document.getElementById('certEventForm');
        form.reset();
        form.action = '<?= base_url('admin/cert-events/store') ?>';
        document.querySelector('#certEventModal .modal-title').textContent = 'สร้างกิจกรรมใหม่';
    });
</script>

<style>
    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 12px;
    }

    .badge-secondary {
        background: #6c757d;
        color: white;
    }

    .badge-success {
        background: #28a745;
        color: white;
    }

    .badge-primary {
        background: #007bff;
        color: white;
    }

    .badge-warning {
        background: #ffc107;
        color: black;
    }

    .badge-danger {
        background: #dc3545;
        color: white;
    }

    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 12px;
    }

    .alert {
        padding: 1rem;
        border-radius: 4px;
    }

    .alert-info {
        background: #e3f2fd;
        color: #0d47a1;
    }
</style>
<?= $this->endSection() ?>