<?php $this->layout('layouts/auth') ?>

<form method="POST" action="/login" class="auth-form">
    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= $this->e($error) ?>
    </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Mật khẩu</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
    </div>
    
    <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
    
    <div class="text-center mt-3">
        <a href="/forgot-password" class="text-muted small">Quên mật khẩu?</a>
    </div>
</form>