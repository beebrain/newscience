<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: #FFD700;
            --color-primary-dark: #DAA520;
            --color-dark: #1A1A1A;
            --color-gray-300: #D1D5DB;
            --color-gray-500: #6B7280;
            --color-gray-700: #374151;
            --color-white: #FFFFFF;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--color-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-wrap {
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .login-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
            border-radius: 14px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-logo svg {
            width: 30px;
            height: 30px;
            stroke: var(--color-dark);
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            color: var(--color-white);
            margin-bottom: 0.35rem;
        }
        
        .login-header p {
            color: var(--color-gray-500);
            font-size: 0.875rem;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
        }
        
        .alert-error {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        
        .alert-success {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--color-gray-500);
            font-size: 0.875rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid var(--color-gray-300);
            border-radius: 8px;
            background: var(--color-white);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: var(--color-primary);
            color: var(--color-dark);
            transition: background 0.2s, transform 0.2s;
        }
        
        .btn:hover {
            background: var(--color-primary-dark);
            transform: translateY(-1px);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.25rem;
            color: var(--color-gray-500);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .back-link:hover {
            color: var(--color-primary);
        }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-header">
            <div class="login-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h1>Admin Login</h1>
            <p>Sign in to manage your university website</p>
        </div>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="<?= base_url('admin/login') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="login" class="form-label">Username หรือ Email</label>
                <input type="text" id="login" name="login" class="form-control" 
                       value="<?= esc(old('login')) ?>" placeholder="admin" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="••••••••" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn">Sign In</button>
        </form>
        
        <a href="<?= base_url() ?>" class="back-link">← Back to Website</a>
    </div>
</body>
</html>
