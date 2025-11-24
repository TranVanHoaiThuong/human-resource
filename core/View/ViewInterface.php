<?php

namespace App\Core\View;

/**
 * View Interface
 * 
 * Interface định nghĩa contract cho View rendering
 */
interface ViewInterface
{
    /**
     * Render view và trả về HTML string
     * 
     * @param string $template Tên template (vd: 'home/index')
     * @param array $data Dữ liệu truyền vào view
     * @return string HTML content
     */
    public function render(string $template, array $data = []): string;

    /**
     * Set page title
     * 
     * @param string $title Page title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Thêm data vào view
     * 
     * @param string $key Key của data
     * @param mixed $value Giá trị
     * @return self
     */
    public function with(string $key, mixed $value): self;

    /**
     * Thêm nhiều data vào view
     * 
     * @param array $data Mảng data
     * @return self
     */
    public function withData(array $data): self;
}

