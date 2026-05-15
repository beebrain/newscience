<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-body">
        <?= view('cert_events/_show', [
            'event'      => $event,
            'recipients' => $recipients,
            'students'   => $students,
            'cert_base'  => $cert_base ?? '',
        ]) ?>
    </div>
</div>
<?= $this->endSection() ?>
