const GLOBAL_APP_SELECTORS = {
    body: 'body',
    appSidebar: 'app-sidebar',
    appContent: 'app-content',
    sidebarToggle: '.sidebar-toggle',
    dropdown: '.dropdown-toggle',
    dropdownToggleBtn: '.dropdown-toggle-btn'
}

// Ajax setup
$.ajaxSetup({
    beforeSend: function() {

    },
    complete: function() {

    },
    error: function(xhr) {
        console.log(xhr);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById(GLOBAL_APP_SELECTORS.appSidebar);
    const appContent = document.getElementById(GLOBAL_APP_SELECTORS.appContent);
    const sidebarToggle = document.querySelector(GLOBAL_APP_SELECTORS.sidebarToggle);
    let isHoverExpanded = false;
    
    // Khôi phục trạng thái
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        collapseSidebar(true);
    }
    
    // Toggle sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidebar.dataset.collapsed === 'true';
            if (isCollapsed) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
        });
    }

    sidebar.addEventListener('mouseenter', function() {
        if (sidebar.dataset.collapsed === 'true' && !isHoverExpanded) {
            isHoverExpanded = true;
            expandSidebar(true, true);
        }
    });
    
    sidebar.addEventListener('mouseleave', function() {
        if (isHoverExpanded) {
            isHoverExpanded = false;
            collapseSidebar(true, true);
        }
    });
    
    function collapseSidebar(animate = true, hover = false) {
        if (!animate) {
            sidebar.style.transition = 'none';
            appContent.style.transition = 'none';
        }
        
        // Sidebar thu nhỏ
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-16');
        sidebar.dataset.collapsed = 'true';
        
        // Content mở rộng
        appContent.classList.remove('ml-64');
        appContent.classList.add('ml-16');
        
        // Ẩn text
        sidebar.querySelectorAll('.nav-text').forEach(el => el.classList.add('hidden'));
        sidebar.querySelectorAll('.toggle-icon').forEach(el => el.classList.add('hidden'));
        
        // Đóng tất cả submenu
        sidebar.querySelectorAll('.submenu').forEach(el => el.classList.add('hidden'));
        sidebar.querySelectorAll('.toggle-icon').forEach(el => el.classList.remove('rotate-90'));
        
        // Căn giữa items
        sidebar.querySelectorAll('.nav-item > a, .submenu-toggle').forEach(el => {
            el.classList.add('justify-center');
            el.classList.remove('justify-between');
        });
        sidebar.querySelectorAll('.submenu-toggle > div').forEach(el => el.classList.add('hidden'));
        sidebar.querySelectorAll('.submenu-toggle').forEach(el => {
            // Thêm icon vào ngoài để hiện khi collapsed
            const icon = el.querySelector('div > i');
            if (icon && !el.querySelector(':scope > i.collapsed-icon')) {
                const clonedIcon = icon.cloneNode(true);
                clonedIcon.classList.add('collapsed-icon');
                el.appendChild(clonedIcon);
            }
            el.querySelectorAll(':scope > i.collapsed-icon').forEach(i => i.classList.remove('hidden'));
        });
        
        if (!hover) {
            localStorage.setItem('sidebarCollapsed', 'true');
        }
        
        if (!animate) {
            requestAnimationFrame(() => {
                sidebar.style.transition = '';
                appContent.style.transition = '';
            });
        }
    }
    
    function expandSidebar(animate = true, hover = false) {
        if (!animate) {
            sidebar.style.transition = 'none';
            appContent.style.transition = 'none';
        }
        
        // Sidebar mở rộng
        sidebar.classList.remove('w-16');
        sidebar.classList.add('w-64');
        sidebar.dataset.collapsed = 'false';
        
        // Content thu về
        appContent.classList.remove('ml-16');
        appContent.classList.add('ml-64');
        
        // Hiện text
        sidebar.querySelectorAll('.nav-text').forEach(el => el.classList.remove('hidden'));
        sidebar.querySelectorAll('.toggle-icon').forEach(el => el.classList.remove('hidden'));
        
        // Reset alignment
        sidebar.querySelectorAll('.nav-item > a').forEach(el => {
            el.classList.remove('justify-center');
        });
        sidebar.querySelectorAll('.submenu-toggle').forEach(el => {
            el.classList.remove('justify-center');
            el.classList.add('justify-between');
        });
        sidebar.querySelectorAll('.submenu-toggle > div').forEach(el => el.classList.remove('hidden'));
        sidebar.querySelectorAll('.collapsed-icon').forEach(el => el.classList.add('hidden'));
        
        if (!hover) {
            localStorage.setItem('sidebarCollapsed', 'false');
        }
        
        if (!animate) {
            requestAnimationFrame(() => {
                sidebar.style.transition = '';
                appContent.style.transition = '';
            });
        }
    }
    
    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (sidebar.dataset.collapsed === 'true' && !isHoverExpanded) return;
            
            const parentLi = this.closest('.has-submenu');
            const submenu = parentLi.querySelector(':scope > .submenu');
            const icon = this.querySelector('.toggle-icon');
            
            if (submenu) {
                const isHidden = submenu.classList.contains('hidden');
                
                if (isHidden) {
                    submenu.classList.remove('hidden');
                    submenu.style.maxHeight = '0';
                    submenu.style.overflow = 'hidden';
                    requestAnimationFrame(() => {
                        submenu.style.transition = 'max-height 0.3s ease-out';
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    });
                    if (icon) icon.classList.add('rotate-90');
                    setTimeout(() => {
                        submenu.style.maxHeight = '';
                        submenu.style.overflow = '';
                        submenu.style.transition = '';
                    }, 300);
                } else {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    submenu.style.overflow = 'hidden';
                    requestAnimationFrame(() => {
                        submenu.style.transition = 'max-height 0.3s ease-out';
                        submenu.style.maxHeight = '0';
                    });
                    if (icon) icon.classList.remove('rotate-90');
                    setTimeout(() => {
                        submenu.classList.add('hidden');
                        submenu.style.maxHeight = '';
                        submenu.style.overflow = '';
                        submenu.style.transition = '';
                    }, 300);
                }
            }
        });
    });
});

$(document).ready(function() {
    // Dropdown toggle
    $(document).on('click', GLOBAL_APP_SELECTORS.dropdownToggleBtn, function(e) {
        e.stopPropagation();
        const dropdownId = $(this).data('dropdown');
        $(GLOBAL_APP_SELECTORS.dropdown).each(function() {
            if ($(this).attr('id') !== dropdownId) {
                $(this).removeClass('show');
            }
        });
        if (!dropdownId) return;
        const dropdown = $('#' + dropdownId);
        if (dropdown.length === 0) return;
        dropdown.toggleClass('show');
    });

    $(document).on('click', function(e) {
        const isClickInsideDropdown = $(e.target).closest(GLOBAL_APP_SELECTORS.dropdown).length > 0;
        const isClickOnToggleBtn = $(e.target).closest(GLOBAL_APP_SELECTORS.dropdownToggleBtn).length > 0;
        
        if (!isClickInsideDropdown && !isClickOnToggleBtn) {
            $(GLOBAL_APP_SELECTORS.dropdown).removeClass('show');
        }
    });
});