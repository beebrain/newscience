<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>Events Coming Up</h2>
        <a href="<?= base_url('admin/events/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Event
        </a>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($events)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <h3>No events yet</h3>
                <p>Create your first event to promote on the homepage and events page.</p>
                <a href="<?= base_url('admin/events/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">Create Event</a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <?php if (!empty($event['featured_image'])): ?>
                                    <img src="<?= base_url('serve/uploads/events/' . $event['featured_image']) ?>"
                                        alt="" class="table-image">
                                <?php else: ?>
                                    <div style="width: 60px; height: 40px; background: #E5E7EB; border-radius: 4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
                            <td><?= $event['event_time'] ? date('g:i A', strtotime($event['event_time'])) : '—' ?></td>
                            <td>
                                <strong><?= esc($event['title']) ?></strong>
                                <?php if (!empty($event['excerpt'])): ?>
                                    <br><small style="color: #6B7280;"><?= esc(substr($event['excerpt'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($event['location'] ?: '—') ?></td>
                            <td>
                                <span class="badge <?= $event['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst($event['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= base_url('admin/events/edit/' . $event['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/events/delete/' . $event['id']) ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
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