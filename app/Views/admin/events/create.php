<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>Create Event</h2>
        <a href="<?= base_url('admin/events') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to List
        </a>
    </div>

    <div class="card-body">
        <form action="<?= base_url('admin/events/store') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" id="title" name="title" class="form-control"
                    value="<?= old('title') ?>" placeholder="Event title" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date" class="form-label">Event Date *</label>
                    <input type="date" id="event_date" name="event_date" class="form-control"
                        value="<?= old('event_date') ?>" required>
                </div>
                <div class="form-group">
                    <label for="event_time" class="form-label">Start Time</label>
                    <input type="time" id="event_time" name="event_time" class="form-control"
                        value="<?= old('event_time') ?>">
                </div>
                <div class="form-group">
                    <label for="event_end_date" class="form-label">End Date</label>
                    <input type="date" id="event_end_date" name="event_end_date" class="form-control"
                        value="<?= old('event_end_date') ?>">
                </div>
                <div class="form-group">
                    <label for="event_end_time" class="form-label">End Time</label>
                    <input type="time" id="event_end_time" name="event_end_time" class="form-control"
                        value="<?= old('event_end_time') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-control"
                    value="<?= old('location') ?>" placeholder="e.g. Main Auditorium">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= old('status') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="featured_image" class="form-label">Featured Image</label>
                    <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="form-group">
                <label for="excerpt" class="form-label">Excerpt</label>
                <textarea id="excerpt" name="excerpt" class="form-control" rows="3"
                    placeholder="Brief description for cards and listings"><?= old('excerpt') ?></textarea>
            </div>

            <div class="form-group">
                <label for="content" class="form-label">Content</label>
                <textarea id="content" name="content" class="form-control" rows="10"
                    placeholder="Full event description (optional)"><?= old('content') ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Save Event</button>
                <a href="<?= base_url('admin/events') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>