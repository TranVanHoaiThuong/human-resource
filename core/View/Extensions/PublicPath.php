<?php

namespace App\Core\View\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PublicPath implements ExtensionInterface {
    private string $path = '/public/';

    public function __construct() {}

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
        return $this->path . $filePath;
    }
}