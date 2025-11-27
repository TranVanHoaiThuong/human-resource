<?php

namespace App\Core\View\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class AssetsPath implements ExtensionInterface {
    private string $path = 'assets';

    public function __construct() {}

    public function register(Engine $engine)
    {
        $engine->registerFunction('asset', [$this, 'assetsPath']);
    }

    public function assetsPath(string $path): string
    {
        $filePath = $this->path . DIRECTORY_SEPARATOR . ltrim($path, '/');

        $lastUpdated = filemtime($filePath);
        $pathInfo = pathinfo($filePath);

        if ($pathInfo['dirname'] === '.') {
            $directory = '';
        } elseif ($pathInfo['dirname'] === DIRECTORY_SEPARATOR) {
            $directory = '/';
        } else {
            $directory = $pathInfo['dirname'] . '/';
        }

        return DIRECTORY_SEPARATOR . $directory . $pathInfo['filename'] . '.' . $pathInfo['extension'] . '?v=' . $lastUpdated;
    }
}