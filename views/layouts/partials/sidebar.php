<aside class="app-sidebar">
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'dashboard' ? 'active' : '' ?>" 
                   href="/dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Quản lý nhân viên -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'employees' ? 'active' : '' ?>" 
                   href="/employees">
                    <i class="bi bi-people"></i>
                    <span>Nhân viên</span>
                </a>
            </li>
            
            <!-- Phòng ban -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'departments' ? 'active' : '' ?>" 
                   href="/departments">
                    <i class="bi bi-building"></i>
                    <span>Phòng ban</span>
                </a>
            </li>
            
            <!-- Chấm công -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'attendance' ? 'active' : '' ?>" 
                   href="/attendance">
                    <i class="bi bi-calendar-check"></i>
                    <span>Chấm công</span>
                </a>
            </li>
            
            <!-- Nghỉ phép -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'leaves' ? 'active' : '' ?>" 
                   href="/leaves">
                    <i class="bi bi-calendar-x"></i>
                    <span>Nghỉ phép</span>
                </a>
            </li>
            
            <!-- Lương -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'payroll' ? 'active' : '' ?>" 
                   href="/payroll">
                    <i class="bi bi-cash-stack"></i>
                    <span>Lương</span>
                </a>
            </li>
            
            <!-- Báo cáo -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'reports' ? 'active' : '' ?>" 
                   href="/reports">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Báo cáo</span>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <!-- Cài đặt -->
            <li class="nav-item">
                <a class="nav-link <?= $active_menu === 'settings' ? 'active' : '' ?>" 
                   href="/settings">
                    <i class="bi bi-gear"></i>
                    <span>Cài đặt</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>