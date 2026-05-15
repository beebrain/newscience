<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <?= view('cert_events/_show', [
        'event'      => $event,
        'recipients' => $recipients,
        'students'   => $students,
        'cert_base'  => $cert_base ?? '',
    ]) ?>
</div>
<?= $this->endSection() ?>
