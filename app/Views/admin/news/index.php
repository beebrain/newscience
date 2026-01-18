<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>All News Articles</h2>
        <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add News
        </a>
    </div>
    
    <div class="card-body" style="padding: 0;">
        <?php if (empty($news)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                </svg>
                <h3>No news articles yet</h3>
                <p>Create your first news article to get started.</p>
                <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">Create News</a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $article): ?>
                        <tr>
                            <td>
                                <?php if ($article['featured_image']): ?>
                                    <img src="<?= base_url('uploads/news/' . $article['featured_image']) ?>" 
                                         alt="" class="table-image">
                                <?php else: ?>
                                    <div style="width: 60px; height: 40px; background: #E5E7EB; border-radius: 4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= esc($article['title']) ?></strong>
                                <?php if ($article['excerpt']): ?>
                                    <br><small style="color: #6B7280;"><?= esc(substr($article['excerpt'], 0, 60)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($article['gf_name'] . ' ' . $article['gl_name']) ?></td>
                            <td>
                                <span class="badge <?= $article['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst($article['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($article['created_at'])) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= base_url('admin/news/edit/' . $article['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/news/delete/' . $article['id']) ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
