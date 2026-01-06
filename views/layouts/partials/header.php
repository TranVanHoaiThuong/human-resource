<header class="app-header h-16 bg-white shadow-sm border-b border-gray-200 flex-shrink-0">
    <div class="w-full h-full px-6">
        <div class="flex items-center justify-between h-full">
            <div class="flex items-center gap-2">
                <button type="button" class="sidebar-toggle p-1 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="h-[30px] bg-gray-300 w-[1px]"></div>
                <div class="header-search w-[300px]">
                    <div class="flex items-center px-4 py-[6px] border border-gray-300 rounded-2xl w-full gap-2">
                        <input id="global-search" class="w-full text-sm text-gray-700 outline-none" placeholder="<?= trans('common.type_to_search') ?>"/>
                        <i class="fa-solid fa-magnifying-glass text-gray-500 text-sm"></i>
                    </div>
                </div>
            </div>
            <!-- Header action -->
            <div class="flex items-center gap-2">
                <!-- Notification dropdown -->
                <div class="relative">
                    <button type="button" class="dropdown-toggle-btn p-2 hover:bg-gray-100 rounded-lg" data-dropdown="notification-dropdown">
                        <i class="fa-solid fa-bell text-xl"></i>
                        <span class="absolute top-0 right-0 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-xs">5</span>
                    </button>

                    <div id="notification-dropdown" class="dropdown-toggle absolute right-0 mt-2 w-max bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                        <div class="block px-4 py-2 font-medium text-center text-gray-700 rounded-t-lg bg-gray-50">
                            Notifications
                        </div>
                        <div class="divide-y divide-gray-100">
                            <a href="#" class="flex px-4 py-3 hover:bg-gray-100">
                                <div class="shrink-0">
                                    <img class="rounded-full w-11 h-11" src="/images/people/profile-picture-1.jpg" alt="Jese image">
                                    <div class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-blue-600 border border-white rounded-full">
                                    <svg class="w-2 h-2 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                                        <path d="M1 18h16a1 1 0 0 0 1-1v-6h-4.439a.99.99 0 0 0-.908.6 3.978 3.978 0 0 1-7.306 0 .99.99 0 0 0-.908-.6H0v6a1 1 0 0 0 1 1Z"/>
                                        <path d="M4.439 9a2.99 2.99 0 0 1 2.742 1.8 1.977 1.977 0 0 0 3.638 0A2.99 2.99 0 0 1 13.561 9H17.8L15.977.783A1 1 0 0 0 15 0H3a1 1 0 0 0-.977.783L.2 9h4.239Z"/>
                                    </svg>
                                    </div>
                                </div>
                                <div class="w-full ps-3">
                                    <div class="text-gray-500 text-sm mb-1.5">New message from <span class="font-semibold text-gray-900">Jese Leos</span>: "Hey, what's up? All set for the presentation?"</div>
                                    <div class="text-xs text-blue-600">a few moments ago</div>
                                </div>
                            </a>
                            <a href="#" class="flex px-4 py-3 hover:bg-gray-100">
                                <div class="shrink-0">
                                    <img class="rounded-full w-11 h-11" src="/images/people/profile-picture-2.jpg" alt="Joseph image">
                                    <div class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-gray-900 border border-white rounded-full">
                                    <svg class="w-2 h-2 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                                        <path d="M6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Zm11-3h-2V5a1 1 0 0 0-2 0v2h-2a1 1 0 1 0 0 2h2v2a1 1 0 0 0 2 0V9h2a1 1 0 1 0 0-2Z"/>
                                    </svg>
                                    </div>
                                </div>
                                <div class="w-full ps-3">
                                    <div class="text-gray-500 text-sm mb-1.5 dark:text-gray-400"><span class="font-semibold text-gray-900">Joseph Mcfall</span> and <span class="font-medium text-gray-900">5 others</span> started following you.</div>
                                    <div class="text-xs text-blue-600 dark:text-blue-500">10 minutes ago</div>
                                </div>
                                </a>
                            <a href="#" class="flex px-4 py-3 hover:bg-gray-100">
                                <div class="shrink-0">
                                    <img class="rounded-full w-11 h-11" src="/images/people/profile-picture-3.jpg" alt="Bonnie image">
                                    <div class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-red-600 border border-white rounded-full">
                                    <svg class="w-2 h-2 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                                        <path d="M17.947 2.053a5.209 5.209 0 0 0-3.793-1.53A6.414 6.414 0 0 0 10 2.311 6.482 6.482 0 0 0 5.824.5a5.2 5.2 0 0 0-3.8 1.521c-1.915 1.916-2.315 5.392.625 8.333l7 7a.5.5 0 0 0 .708 0l7-7a6.6 6.6 0 0 0 2.123-4.508 5.179 5.179 0 0 0-1.533-3.793Z"/>
                                    </svg>
                                    </div>
                                </div>
                                <div class="w-full ps-3">
                                    <div class="text-gray-500 text-sm mb-1.5 dark:text-gray-400"><span class="font-semibold text-gray-900">Bonnie Green</span> and <span class="font-medium text-gray-900">141 others</span> love your story. See it and view more stories.</div>
                                    <div class="text-xs text-blue-600 dark:text-blue-500">44 minutes ago</div>
                                </div>
                            </a>
                            <a href="#" class="flex px-4 py-3 hover:bg-gray-100">
                                <div class="shrink-0">
                                    <img class="rounded-full w-11 h-11" src="/images/people/profile-picture-4.jpg" alt="Leslie image">
                                    <div class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-green-400 border border-white rounded-full dark:border-gray-800">
                                    <svg class="w-2 h-2 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                                        <path d="M18 0H2a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h2v4a1 1 0 0 0 1.707.707L10.414 13H18a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm-5 4h2a1 1 0 1 1 0 2h-2a1 1 0 1 1 0-2ZM5 4h5a1 1 0 1 1 0 2H5a1 1 0 0 1 0-2Zm2 5H5a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Zm9 0h-6a1 1 0 0 1 0-2h6a1 1 0 1 1 0 2Z"/>
                                    </svg>
                                    </div>
                                </div>
                                <div class="w-full ps-3">
                                    <div class="text-gray-500 text-sm mb-1.5 dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-white">Leslie Livingston</span> mentioned you in a comment: <span class="font-medium text-blue-500" href="#">@bonnie.green</span> what do you say?</div>
                                    <div class="text-xs text-blue-600 dark:text-blue-500">1 hour ago</div>
                                </div>
                            </a>
                        </div>
                        <a href="#" class="block py-2 text-sm font-medium text-center text-gray-900 rounded-b-lg bg-gray-50 hover:bg-gray-100">
                            <div class="inline-flex items-center ">
                            <svg class="w-4 h-4 me-2 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 14">
                                <path d="M10 0C4.612 0 0 5.336 0 7c0 1.742 3.546 7 10 7 6.454 0 10-5.258 10-7 0-1.664-4.612-7-10-7Zm0 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
                            </svg>
                                View all
                            </div>
                        </a>
                    </div>
                </div>
                <!-- User dropdown -->
                <div class="relative">
                    <button type="button" class="dropdown-toggle-btn flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg" data-dropdown="user-menu-dropdown">
                        <i class="fa-solid fa-user text-xl"></i>
                    </button>
                    
                    <div id="user-menu-dropdown" class="dropdown-toggle absolute right-0 mt-2 w-max bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                        <span class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Xin chào, <?= $this->e($user['fullname'] ?? 'User') ?></span>
                        <hr class="my-1 border-gray-200">
                        <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Hồ sơ</a>
                        <a href="/settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Cài đặt</a>
                        <hr class="my-1 border-gray-200">
                        <a href="javascript:;" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <?= trans('auth.signout') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="logout-form" action="/logout" method="POST" style="display:none;"></form>
</header>