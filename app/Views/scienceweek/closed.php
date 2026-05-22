<?= $this->extend('scienceweek/layout') ?>
<?= $this->section('content') ?>

<div class="card border-danger">
    <div class="card-body text-center py-5">
        <div style="font-size: 3rem;">🚫</div>
        <h3 class="text-danger mt-2"><?= esc($comp['name_th']) ?></h3>
        <p class="text-muted"><?= esc($message) ?></p>
        <a href="<?= base_url('scienceweek') ?>" class="btn btn-outline-primary">กลับหน้าหลัก</a>
    </div>
</div>

<?= $this->endSection() ?>
