<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>Edit News Article</h2>
        <a href="<?= base_url('admin/news') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to List
        </a>
    </div>
    
    <div class="card-body">
        <form action="<?= base_url('admin/news/update/' . $news['id']) ?>" method="post" enctype="multipart/form-data" id="newsForm">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= old('title', $news['title']) ?>" placeholder="Enter news title" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="draft" <?= old('status', $news['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= old('status', $news['status']) === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="featured_image" class="form-label">Featured Image</label>
                    <?php if ($news['featured_image']): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <img src="<?= base_url('uploads/news/' . $news['featured_image']) ?>" 
                                 alt="" style="max-width: 200px; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
                    <small style="color: #6B7280;">Leave empty to keep current image</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="excerpt" class="form-label">Excerpt</label>
                <textarea id="excerpt" name="excerpt" class="form-control" rows="3" 
                          placeholder="Brief summary of the article"><?= old('excerpt', $news['excerpt']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="content" class="form-label">Content *</label>
                <textarea id="content" name="content" class="form-control" rows="15" 
                          placeholder="Write your article content here..." required><?= old('content', $news['content']) ?></textarea>
            </div>
            
            <!-- Existing Images -->
            <?php if (!empty($images)): ?>
            <div class="form-group">
                <label class="form-label">Current Images</label>
                <div class="image-preview-grid" id="existingImages">
                    <?php foreach ($images as $image): ?>
                        <div class="image-preview-item" data-id="<?= $image['id'] ?>">
                            <img src="<?= base_url('uploads/news/' . $image['image_path']) ?>" alt="">
                            <button type="button" class="remove-btn delete-existing" data-id="<?= $image['id'] ?>">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Additional Images -->
            <div class="form-group">
                <label class="form-label">Add More Images</label>
                <div class="file-upload" id="dropZone">
                    <input type="file" name="images[]" id="additionalImages" multiple accept="image/*">
                    <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <p>Click to upload or drag and drop</p>
                    <small>PNG, JPG, GIF, WebP up to 5MB each</small>
                </div>
                <div class="image-preview-grid" id="imagePreview"></div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Update Article
                </button>
                <a href="<?= base_url('admin/news') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('additionalImages');
    const imagePreview = document.getElementById('imagePreview');
    let selectedFiles = [];
    
    // Click to upload
    dropZone.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#FFD700';
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#D1D5DB';
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#D1D5DB';
        handleFiles(e.dataTransfer.files);
    });
    
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    function handleFiles(files) {
        for (let file of files) {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
                displayPreview(file, selectedFiles.length - 1);
            }
        }
        updateFileInput();
    }
    
    function displayPreview(file, index) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="">
                <button type="button" class="remove-btn" data-index="${index}">×</button>
            `;
            div.querySelector('.remove-btn').addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                imagePreview.innerHTML = '';
                selectedFiles.forEach((f, i) => displayPreview(f, i));
                updateFileInput();
            });
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
    
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }
    
    // Delete existing images
    document.querySelectorAll('.delete-existing').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this image?')) return;
            
            const imageId = this.dataset.id;
            const item = this.closest('.image-preview-item');
            
            fetch('<?= base_url('utility/upload/delete/') ?>' + imageId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                } else {
                    alert(data.message || 'Failed to delete image');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
