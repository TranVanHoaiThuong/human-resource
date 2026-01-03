<aside id="app-sidebar" class="fixed left-0 top-0 h-screen w-64 bg-slate-800 text-white transition-all duration-300 z-40 flex flex-col" data-collapsed="false">
    
    <!-- Logo / Brand - Phần header của sidebar -->
    <div class="h-16 flex items-center justify-center border-b border-slate-700 flex-shrink-0">
        <a href="/" class="flex items-center gap-3">
            <i class="fa-solid fa-building text-2xl text-primary-400"></i>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav flex-1 overflow-y-auto py-4">
        <ul class="space-y-1 px-3">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors <?= $active_menu === 'dashboard' ? 'bg-slate-700 text-primary-400' : '' ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center flex-shrink-0"></i>
                    <span class="nav-text whitespace-nowrap">Dashboard</span>
                </a>
            </li>
            
            <!-- Menu có submenu -->
            <li class="nav-item has-submenu">
                <button type="button" class="submenu-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-users w-5 text-center flex-shrink-0"></i>
                        <span class="nav-text whitespace-nowrap">Nhân sự</span>
                    </div>
                    <i class="fa-solid fa-chevron-right toggle-icon transition-transform duration-200 text-xs"></i>
                </button>
                <ul class="submenu pl-4 mt-1 space-y-1 hidden">
                    <li>
                        <a href="/employees" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors text-sm">
                            <i class="fa-solid fa-user w-4 text-center flex-shrink-0"></i>
                            <span class="nav-text">Nhân viên</span>
                        </a>
                    </li>
                    <li class="has-submenu">
                        <button type="button" class="submenu-toggle w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors text-sm">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-building w-4 text-center flex-shrink-0"></i>
                                <span class="nav-text">Phòng ban</span>
                            </div>
                            <i class="fa-solid fa-chevron-right toggle-icon transition-transform duration-200 text-sm"></i>
                        </button>
                        <ul class="submenu pl-4 mt-1 space-y-1 hidden">
                            <li>
                                <a href="/departments/list" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 transition-colors text-sm">
                                    <i class="fa-solid fa-list w-3 text-center flex-shrink-0"></i>
                                    <span class="nav-text">Danh sách</span>
                                </a>
                            </li>
                            <li>
                                <a href="/departments/tree" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 transition-colors text-sm">
                                    <i class="fa-solid fa-sitemap w-3 text-center flex-shrink-0"></i>
                                    <span class="nav-text">Sơ đồ</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            
            <!-- Các menu khác -->
            <li class="nav-item">
                <a href="/attendance" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-calendar-check w-5 text-center flex-shrink-0"></i>
                    <span class="nav-text whitespace-nowrap">Chấm công</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/leaves" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-calendar-xmark w-5 text-center flex-shrink-0"></i>
                    <span class="nav-text whitespace-nowrap">Nghỉ phép</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/payroll" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-money-bill-wave w-5 text-center flex-shrink-0"></i>
                    <span class="nav-text whitespace-nowrap">Lương</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/reports" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-chart-pie w-5 text-center flex-shrink-0"></i>
                    <span class="nav-text whitespace-nowrap">Báo cáo</span>
                </a>
            </li>
            
            <!-- Divider -->
            <li class="nav-divider border-t border-slate-600 my-3"></li>
            
            <li class="nav-item">
                <a href="/settings" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-gear w-5 text-center flex-shrink-0"></i>
                    <span class="nav-text whitespace-nowrap">Cài đặt</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>