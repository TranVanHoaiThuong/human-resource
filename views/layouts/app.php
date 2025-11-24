<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?></title>
    
    <?= $this->insert('layouts/partials/head') ?>
    
    <!-- Page specific CSS -->
    <?= $this->section('styles') ?>
</head>
<body class="app-layout">
    <!-- Header -->
    <?= $this->insert('layouts/partials/header', [
        'user' => $user ?? null
    ]) ?>
    
    <div class="app-container">
        <!-- Sidebar -->
        <?= $this->insert('layouts/partials/sidebar', [
            'active_menu' => $active_menu ?? ''
        ]) ?>
        
        <!-- Main Content -->
        <main class="app-main">
            <!-- Breadcrumb -->
            <?= $this->insert('layouts/partials/breadcrumb', [
                'breadcrumbs' => $breadcrumbs ?? []
            ]) ?>
            
            <!-- Flash Messages -->
            <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= $this->e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Page Content -->
            <div class="page-content">
                <?= $this->section('content') ?>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?= $this->insert('layouts/partials/footer') ?>
    
    <?= $this->insert('layouts/partials/scripts') ?>
    
    <!-- Page specific JS -->
    <?= $this->section('scripts') ?>
</body>
</html>