<?php
/** @var array<string,mixed> $event */
/** @var string $cert_base */
$cb = $cert_base ?? rtrim(base_url('dashboard/cert-events'), '/');
?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
    <h1 style="font-size:1.4rem;font-weight:700;color:#111827;margin:0 0 1rem;">แก้ไขกิจกรรม: <?= esc((string) ($event['title'] ?? '')) ?></h1>

    <?= view('cert_events/_wizard', [
        'event'      => $event,
        'cert_base'  => $cb,
        'action_url' => $cb . '/' . (int) $event['id'] . '/update',
        'cancel_url' => $cb . '/' . (int) $event['id'],
    ]) ?>
</div>
<?= $this->endSection() ?>
