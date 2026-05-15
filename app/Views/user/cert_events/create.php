<?php
/** @var string $cert_base */
$cb = $cert_base ?? rtrim(base_url('dashboard/cert-events'), '/');
?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
    <h1 style="font-size:1.4rem;font-weight:700;color:#111827;margin:0 0 1rem;">สร้างกิจกรรมใบรับรอง</h1>

    <?= view('cert_events/_wizard', [
        'event'      => null,
        'cert_base'  => $cb,
        'action_url' => $cb . '/store',
        'cancel_url' => $cb,
    ]) ?>
</div>
<?= $this->endSection() ?>
