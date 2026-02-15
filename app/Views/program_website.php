<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?></title>
    <meta name="description" content="<?= esc($program_page['meta_description'] ?? $program['name_th'] ?? '') ?>">
    
    <!-- Base Styles -->
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
        
        .program-site-container {
            min-height: 100vh;
        }
        
        .program-block {
            width: 100%;
        }
        
        .program-block img {
            max-width: 100%;
            height: auto;
        }
        
        /* Default responsive styles */
        @media (max-width: 768px) {
            .program-block {
                padding: 1rem !important;
            }
        }
    </style>
    
    <!-- Custom CSS from content blocks -->
    <style>
        <?= $custom_css ?>
    </style>
    
    <?php if (isset($program_page['theme_color'])): ?>
    <style>
        :root {
            --program-primary: <?= $program_page['theme_color'] ?>;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <div class="program-site-container">
        <?php foreach ($blocks as $block): ?>
            <div class="program-block" data-block-key="<?= esc($block['key']) ?>">
                <?= $block['content'] ?>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($blocks)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: #666;">
                <h2>เว็บไซต์อยู่ระหว่างการพัฒนา</h2>
                <p>โปรดกลับมาใหม่ในภายหลัง</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Custom JS from content blocks -->
    <script>
        <?= $custom_js ?>
    </script>
</body>
</html>
