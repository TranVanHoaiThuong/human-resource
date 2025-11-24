<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= $this->asset('/bootstrap-5/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= $wwwroot ?>/assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo text-center mb-4">
                <img src="<?= $wwwroot ?>/assets/images/logo.png" alt="HRM" height="60">
                <h3 class="mt-3">HRM System</h3>
            </div>
            
            <!-- Content -->
            <?= $this->section('content') ?>
        </div>
        
        <!-- Footer -->
        <div class="auth-footer text-center mt-4">
            <p class="text-muted">&copy; <?= date('Y') ?> HRM System</p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?= $this->asset('/bootstrap-5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>