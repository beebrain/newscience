<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <h1>Import Results</h1>
    
    <div class="card mt-4">
        <div class="card-body">
            <?php if ($results['success']): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> Data import completed successfully.
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> Data import failed.
            </div>
            <?php endif; ?>
            
            <h3>Messages</h3>
            <ul class="list-group mb-4">
                <?php foreach ($results['messages'] as $message): ?>
                <li class="list-group-item"><?= esc($message) ?></li>
                <?php endforeach; ?>
            </ul>
            
            <h3>Import Summary</h3>
            <table class="table">
                <tr>
                    <th>Site Settings</th>
                    <td><?= $results['counts']['site_settings'] ?></td>
                </tr>
                <tr>
                    <th>Departments</th>
                    <td><?= $results['counts']['departments'] ?></td>
                </tr>
                <tr>
                    <th>Programs</th>
                    <td><?= $results['counts']['programs'] ?></td>
                </tr>
                <tr>
                    <th>News Articles</th>
                    <td><?= $results['counts']['news'] ?></td>
                </tr>
            </table>
            
            <a href="<?= base_url() ?>" class="btn btn-primary">Go to Homepage</a>
            <a href="<?= base_url('admin/news') ?>" class="btn btn-secondary">View News</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
