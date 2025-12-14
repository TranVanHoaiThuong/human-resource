<?php

namespace App\Core;

/** Interface cho Service Provider pattern */
interface ServiceProvider
{
    /** Đăng ký services vào container */
    public function register(Container $container): void;

    /** Boot services sau khi tất cả providers đã được đăng ký */
    public function boot(Container $container): void;
}

