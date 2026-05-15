<?php
/** @var array<string,mixed> $event */
/** @var string $cert_base */
$cb = $cert_base ?? rtrim(base_url('admin/cert-events'), '/');
?>
<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin:0;">แก้ไขกิจกรรม: <?= esc((string) ($event['title'] ?? '')) ?></h2>
    </div>
    <div class="card-body">
        <?= view('cert_events/_wizard', [
            'event'      => $event,
            'cert_base'  => $cb,
            'action_url' => $cb . '/' . (int) $event['id'] . '/update',
            'cancel_url' => $cb . '/' . (int) $event['id'],
        ]) ?>
    </div>
</div>
<?= $this->endSection() ?>
