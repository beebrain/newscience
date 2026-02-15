<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.875rem;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .preview-banner a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        
        .preview-banner a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .site-container {
            margin-top: 50px;
            min-height: calc(100vh - 50px);
        }
        
        .program-block {
            width: 100%;
        }
        
        .program-block img {
            max-width: 100%;
            height: auto;
        }
        
        .unpublished-label {
            background: #fbbf24;
            color: #92400e;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .program-block {
                padding: 1rem !important;
            }
        }
    </style>
    
    <style>
        <?= $custom_css ?>
    </style>
</head>
<body>
    <div class="preview-banner">
        <span>👁️ โหมดตัวอย่าง - เนื้อหานี้มองเห็นเฉพาะประธานหลักสูตร</span>
        <a href="<?= base_url('program-admin/content-builder/' . $program['id']) ?>">✕ ปิดตัวอย่าง</a>
    </div>
    
    <div class="site-container">
        <?php foreach ($blocks as $block): ?>
            <div class="program-block" data-block-key="<?= esc($block['block_key']) ?>">
                <?php if (!$block['is_published']): ?>
                    <div class="unpublished-label">⚠️ บล็อกนี้ยังไม่ได้เผยแพร่</div>
                <?php endif; ?>
                <?= $block['content'] ?>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($blocks)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: #666;">
                <h2>ยังไม่มีบล็อกที่ถูกเผยแพร่</h2>
                <p>กรุณาสร้างบล็อกเนื้อหาใน Content Builder</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        <?= $custom_js ?>
    </script>
</body>
</html>
