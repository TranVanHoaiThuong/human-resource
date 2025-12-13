<?php

namespace App\Core;

/**
 * Service Provider Interface
 * 
 * Service Provider pattern giúp tổ chức việc đăng ký services một cách có cấu trúc.
 * Mỗi service provider chịu trách nhiệm đăng ký một nhóm services liên quan.
 * 
 * @example
 * class DatabaseServiceProvider implements ServiceProvider {
 *     public function register(Container $container): void {
 *         $container->singleton('db', fn($c) => new Database(...));
 *     }
 * }
 */
interface ServiceProvider
{
    /**
     * Đăng ký services vào container
     * 
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void;

    /**
     * Boot services sau khi tất cả providers đã được đăng ký
     * Dùng để thực hiện các tác vụ cần services khác
     * 
     * @param Container $container
     * @return void
     */
    public function boot(Container $container): void;
}

