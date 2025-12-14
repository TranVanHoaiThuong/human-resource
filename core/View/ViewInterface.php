<?php

namespace App\Core\View;

/** Interface định nghĩa contract cho View rendering */
interface ViewInterface
{
    /** Render view và trả về HTML string */
    public function render(string $template, array $data = []): string;

    /** Set page title */
    public function setTitle(string $title): self;

    /** Thêm data vào view */
    public function with(string $key, mixed $value): self;

    /** Thêm nhiều data vào view */
    public function withData(array $data): self;
}

