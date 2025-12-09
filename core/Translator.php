<?php

namespace App\Core;

/**
 * Translator - Hệ thống đa ngôn ngữ
 * 
 * Hỗ trợ:
 * - Load translations theo module
 * - Placeholder replacement
 * - Fallback language
 * - Cache loaded translations
 */
class Translator
{
    /**
     * Ngôn ngữ hiện tại
     */
    protected string $locale = 'vi';
    
    /**
     * Ngôn ngữ fallback khi không tìm thấy translation
     */
    protected string $fallback = 'en';
    
    /**
     * Đường dẫn thư mục lang
     */
    protected string $path;
    
    /**
     * Cache các translations đã load
     */
    protected array $loaded = [];

    public function __construct(string $basePath, string $locale = 'vi')
    {
        $this->path = rtrim($basePath, '/') . '/lang';
        $this->locale = $locale;
    }

    /**
     * Lấy translation
     * 
     * @param string $key Dạng "module.key" hoặc "module.nested.key"
     * @param array $replace Placeholders to replace
     * @param string|null $locale Override locale
     * @return string
     * 
     * @example
     * $t->get('user.username')              // "Tên đăng nhập"
     * $t->get('common.save')                // "Lưu"
     * $t->get('user.welcome', ['name' => 'John'])  // "Chào John"
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;
        
        // Parse key: "module.nested.key" → module = "module", rest = "nested.key"
        $segments = explode('.', $key);
        $module = array_shift($segments);
        $itemKey = implode('.', $segments);
        
        // Load module nếu chưa load
        $this->loadModule($module, $locale);
        
        // Tìm translation
        $line = $this->getLine($locale, $module, $itemKey);
        
        // Fallback nếu không tìm thấy
        if ($line === null && $locale !== $this->fallback) {
            $this->loadModule($module, $this->fallback);
            $line = $this->getLine($this->fallback, $module, $itemKey);
        }
        
        // Trả về key nếu không tìm thấy
        if ($line === null) {
            return $key;
        }
        
        // Replace placeholders
        return $this->replacePlaceholders($line, $replace);
    }

    /**
     * Shorthand cho get()
     */
    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return $this->get($key, $replace, $locale);
    }

    /**
     * Load toàn bộ module translations (để dùng trong JS)
     */
    public function getModule(string $module, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale;
        $this->loadModule($module, $locale);
        
        return $this->loaded[$locale][$module] ?? [];
    }

    /**
     * Set locale
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get available locales
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        $dirs = glob($this->path . '/*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $locales[] = basename($dir);
        }
        
        return $locales;
    }

    /**
     * Load module translation file
     */
    protected function loadModule(string $module, string $locale): void
    {
        // Đã load rồi thì skip
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

    /**
     * Lấy translation từ cache
     */
    protected function getLine(string $locale, string $module, string $key): ?string
    {
        $translations = $this->loaded[$locale][$module] ?? [];
        
        // Support nested keys: "messages.success" → $translations['messages']['success']
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

    /**
     * Replace placeholders trong translation
     * 
     * Hỗ trợ: :name, :count, :value...
     */
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