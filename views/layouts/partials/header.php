<header class="app-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-auto">
                <button class="btn btn-link sidebar-toggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <div class="col">
                <a href="/" class="navbar-brand">
                    HRM System
                </a>
            </div>
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?= $this->e($user['name'] ?? 'User') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="/settings">Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>