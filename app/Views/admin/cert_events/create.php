<?php
/** @var string $cert_base */
$cb = $cert_base ?? rtrim(base_url('admin/cert-events'), '/');
?>
<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin:0;">สร้างกิจกรรมใบรับรอง</h2>
    </div>
    <div class="card-body">
        <?= view('cert_events/_wizard', [
            'event'      => null,
            'cert_base'  => $cb,
            'action_url' => $cb . '/store',
            'cancel_url' => $cb,
        ]) ?>
    </div>
</div>
<?= $this->endSection() ?>
