<?php

namespace App\Core;

/** Hệ thống đa ngôn ngữ với placeholder replacement và fallback */
class Translator
{
    protected string $locale = 'vi';
    protected string $fallback = 'en';
    protected string $path;
    protected array $loaded = [];

    public function __construct(string $basePath, string $locale = 'vi')
    {
        $this->path = rtrim($basePath, '/') . '/lang';
        $this->locale = $locale;
    }

    /** Lấy translation theo key (dạng "module.key") */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        $segments = explode('.', $key);
        $module = array_shift($segments);
        $itemKey = implode('.', $segments);

        $this->loadModule($module, $locale);
        $line = $this->getLine($locale, $module, $itemKey);

        if ($line === null && $locale !== $this->fallback) {
            $this->loadModule($module, $this->fallback);
            $line = $this->getLine($this->fallback, $module, $itemKey);
        }

        if ($line === null) {
            return $key;
        }

        return $this->replacePlaceholders($line, $replace);
    }

    /** Shorthand cho get() */
    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return $this->get($key, $replace, $locale);
    }

    /** Load toàn bộ module translations (cho JS) */
    public function getModule(string $module, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale;
        $this->loadModule($module, $locale);

        return $this->loaded[$locale][$module] ?? [];
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getAvailableLocales(): array
    {
        $locales = [];
        $dirs = glob($this->path . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $locales[] = basename($dir);
        }

        return $locales;
    }

    protected function loadModule(string $module, string $locale): void
    {
        if (isset($this->loaded[$locale][$module])) {
            return;
        }

        $file = "{$this->path}/{$locale}/{$module}.php";

        if (file_exists($file)) {
            $this->loaded[$locale][$module] = require $file;
        } else {
            $this->loaded[$locale][$module] = [];
        }
    }

    protected function getLine(string $locale, string $module, string $key): ?string
    {
        $translations = $this->loaded[$locale][$module] ?? [];

        $segments = explode('.', $key);
        $value = $translations;

        foreach ($segments as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return null;
            }
            $value = $value[$segment];
        }

        return is_string($value) ? $value : null;
    }

    protected function replacePlaceholders(string $line, array $replace): string
    {
        if (empty($replace)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $line
            );
        }

        return $line;
    }
}