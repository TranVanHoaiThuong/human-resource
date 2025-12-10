<?php

namespace App\Core\View\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PublicPath implements ExtensionInterface {
    private string $path = '/public/';
    private string $basePath;

    public function __construct(string $basePath = '') {
        $this->basePath = $basePath ?: dirname(__DIR__, 3);
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('public', [$this, 'publicPath']);
    }

    public function publicPath(string $path): string
    {
        $filePath = ltrim($path, '/');
        if(str_starts_with($filePath, 'js/') && !str_contains($filePath, '.min.js')) {
            $filePath .= '.min.js';
        }
        if(str_starts_with($filePath, 'css/') && !str_contains($filePath, '.min.css')) {
            $filePath .= '.min.css';
        }
        $fullPath = $this->basePath . '/public/' . $filePath;
        $version = '';
        if (file_exists($fullPath)) {
            $version = '?v=' . filemtime($fullPath);
        }
        return $this->path . $filePath . $version;
    }
}