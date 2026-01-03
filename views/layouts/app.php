<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?></title>
    
    <?= $this->insert('layouts/partials/head') ?>
    <?= $this->section('styles') ?>
</head>
<body class="app-layout bg-gray-100" data-page="<?= $this->e($page ?? '') ?>">
    
    <div class="app-wrapper flex min-h-screen">
        <!-- Sidebar - Fixed bên trái, full chiều cao -->
        <?= $this->insert('layouts/partials/sidebar', [
            'active_menu' => $active_menu ?? ''
        ]) ?>
        
        <!-- Content wrapper - Chứa cả header và main -->
        <div id="app-content" class="flex-1 ml-64 flex flex-col min-h-screen transition-all duration-300">
            <!-- Header - Nằm trong content, không fixed -->
            <?= $this->insert('layouts/partials/header', [
                'user' => $user ?? null
            ]) ?>
            
            <!-- Main Content -->
            <main class="app-main flex-1 p-6">
                <!-- Breadcrumb -->
                <?= $this->insert('layouts/partials/breadcrumb', [
                    'breadcrumbs' => $breadcrumbs ?? []
                ]) ?>
                
                <!-- Flash Messages -->
                <?php if (isset($flash)): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4">
                    <?= $this->e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <div class="page-content">
                    <?= $this->section('content') ?>
                </div>
            </main>
            
            <!-- Footer (optional) -->
            <?= $this->insert('layouts/partials/footer') ?>
        </div>
    </div>
    
    <?= $this->insert('layouts/partials/scripts') ?>
    <?= $this->section('scripts') ?>
</body>
</html>