<?php

namespace App\Core\Logging;

/**
 * File Logger Implementation
 * 
 * Ghi log vào file với rotation support
 */
class FileLogger implements Logger
{
    protected array $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];

    public function __construct(
        protected string $logPath,
        protected string $logLevel = 'info',
        protected bool $writeLogs = true
    )
    {
        $this->logPath = rtrim($logPath, '/');
        $this->logLevel = strtolower($logLevel);
        $this->writeLogs = $writeLogs;
        $this->ensureLogDirectory();
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        if (!$this->writeLogs) {
            return;
        }
        // Kiểm tra log level
        if ($this->levels[$level] < ($this->levels[$this->logLevel] ?? 0)) {
            return;
        }

        $logFile = $this->getLogFile($level);
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $log = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
    }

    protected function getLogFile(string $level): string
    {
        $date = date('Y-m-d');
        
        // Critical và error vào file riêng
        if (in_array($level, ['critical', 'error'])) {
            return "{$this->logPath}/{$date}-{$level}.log";
        }
        
        // Các level khác vào file chung
        return "{$this->logPath}/{$date}.log";
    }

    protected function ensureLogDirectory(): void
    {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
}

