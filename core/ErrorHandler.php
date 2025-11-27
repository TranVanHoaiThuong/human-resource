<?php

namespace App\Core;

use League\Plates\Engine;
use Throwable;

/**
 * Error Handler
 * 
 * Xử lý tất cả errors, exceptions và fatal errors trong ứng dụng.
 * 
 * Quy tắc:
 * - APP_DEBUG=true: Show tất cả lỗi chi tiết (bất kể APP_ENV)
 * - APP_DEBUG=false: Ẩn warnings, chỉ show error pages cho fatal errors/exceptions
 */
class ErrorHandler
{
    protected string $basePath;
    protected bool $debug;
    protected string $environment;
    protected ?Engine $viewEngine = null;
    protected bool $writeLogs = false;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->writeLogs = $_ENV['APP_LOG'] === 'true';
        $this->loadEnvironmentSettings();
    }

    /**
     * Load các settings từ environment variables
     */
    protected function loadEnvironmentSettings(): void
    {
        // APP_DEBUG có ưu tiên cao nhất
        $debug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false';
        $this->debug = filter_var($debug, FILTER_VALIDATE_BOOLEAN);
        
        // APP_ENV để xác định môi trường
        $this->environment = strtolower($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production');
    }

    /**
     * Đăng ký error và exception handlers
     */
    public function register(): void
    {
        $this->configurePhpSettings();
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Set view engine (dùng sau khi Application đã bootstrap)
     */
    public function setViewEngine(Engine $engine): void
    {
        $this->viewEngine = $engine;
    }

    /**
     * Cấu hình PHP INI settings
     * 
     * APP_DEBUG=true: Show tất cả errors (kể cả warnings, notices)
     * APP_DEBUG=false: Chỉ report fatal errors, không display
     */
    protected function configurePhpSettings(): void
    {
        if ($this->debug) {
            // Debug mode: Show TẤT CẢ errors và warnings (bất kể APP_ENV)
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            ini_set('log_errors', '1');
            ini_set('error_log', $this->basePath . '/logs/errors.log');
        } else {
            // Non-debug: Chỉ report fatal errors, không display ra màn hình
            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', $this->basePath . '/logs/errors.log');
        }
    }

    /**
     * Handle PHP errors
     * 
     * @param int $severity Error severity level
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number
     * @return bool True to prevent default PHP error handler
     */
    public function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        // Nếu error đã bị suppressed bởi @ operator
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Debug mode: Convert TẤT CẢ errors thành exception để show chi tiết
        if ($this->debug) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }

        // Non-debug: Chỉ throw exception cho fatal errors
        $fatalErrors = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
        if ($severity & $fatalErrors) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }

        // Warnings, notices: Log nhưng KHÔNG show ra màn hình
        $this->logError($message, $file, $line, $severity);
        
        return true; // Ngăn default PHP error handler
    }

    /**
     * Handle uncaught exceptions
     * 
     * @param Throwable $exception
     */
    public function handleException(Throwable $exception): void
    {
        $statusCode = $this->getHttpStatusCode($exception);

        // Log exception (luôn log bất kể debug hay không)
        $this->logException($exception);

        if ($this->debug) {
            // Debug mode: Show chi tiết exception với stack trace
            $this->renderDebugException($exception, $statusCode);
        } else {
            // Non-debug: Render error page đẹp
            $this->renderProductionError($statusCode, $exception);
        }
    }

    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null) {
            $fatalErrors = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR;
            
            if ($error['type'] & $fatalErrors) {
                $this->handleException(new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                ));
            }
        }
    }

    /**
     * Render error view cho production/non-debug mode
     * 
     * @param int $statusCode HTTP status code
     * @param Throwable $exception
     */
    protected function renderProductionError(int $statusCode, Throwable $exception): void
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        
        // Nếu có view engine và view template tồn tại
        if ($this->viewEngine !== null) {
            $viewPath = "errors/{$statusCode}";
            
            if ($this->viewEngine->exists($viewPath)) {
                echo $this->viewEngine->render($viewPath, [
                    'title' => "Error {$statusCode}",
                    'statusCode' => $statusCode,
                    'message' => $this->getErrorTitle($statusCode),
                ]);
                return;
            }
        }
        
        // Fallback: Default error page
        $this->renderDefaultErrorPage($statusCode);
    }

    /**
     * Render default error page khi không có view template
     * 
     * @param int $statusCode
     */
    protected function renderDefaultErrorPage(int $statusCode): void
    {
        $title = $this->getErrorTitle($statusCode);
        
        echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$statusCode}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-container {
            background: white;
            padding: 50px 60px;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #e74c3c;
            line-height: 1;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .error-message {
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .back-btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">{$statusCode}</div>
        <h1 class="error-title">{$title}</h1>
        <p class="error-message">Có lỗi xảy ra trong quá trình xử lý.<br>Vui lòng thử lại sau.</p>
        <button onclick="history.back()" class="back-btn">← Quay lại</button>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render chi tiết exception khi debug mode
     * 
     * @param Throwable $exception
     * @param int $statusCode
     */
    protected function renderDebugException(Throwable $exception, int $statusCode): void
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        
        $exceptionClass = $this->escape(get_class($exception));
        $message = $this->escape($exception->getMessage());
        $file = $this->escape($exception->getFile());
        $line = $exception->getLine();
        $env = $this->escape($this->environment);
        
        echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$exceptionClass}: {$message}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
            background: #1a1a2e;
            color: #eee;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 30px;
            border-radius: 12px 12px 0 0;
            margin-bottom: 0;
        }
        .header-title {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 10px;
        }
        .exception-class {
            font-size: 24px;
            font-weight: 700;
            color: white;
            word-break: break-all;
        }
        .content {
            background: #16213e;
            border-radius: 0 0 12px 12px;
            padding: 30px;
        }
        .message {
            font-size: 18px;
            color: #f39c12;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(243, 156, 18, 0.1);
            border-left: 4px solid #f39c12;
            border-radius: 0 8px 8px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 10px 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
        }
        .info-label { color: #888; }
        .info-value { color: #4ecdc4; word-break: break-all; }
        .section-title {
            font-size: 16px;
            color: #888;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }
        .trace {
            background: #0f0f23;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .trace-item {
            padding: 12px 15px;
            border-bottom: 1px solid #222;
            display: flex;
            gap: 15px;
        }
        .trace-item:last-child { border-bottom: none; }
        .trace-num {
            color: #666;
            min-width: 30px;
        }
        .trace-file { color: #64b5f6; }
        .trace-line { color: #f39c12; }
        .trace-func { color: #4ecdc4; }
        .env-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e74c3c;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">
            Exception <span class="env-badge">{$env}</span>
        </div>
        <div class="exception-class">{$exceptionClass}</div>
    </div>
    
    <div class="content">
        <div class="message">{$message}</div>
        
        <div class="info-grid">
            <span class="info-label">File:</span>
            <span class="info-value">{$file}</span>
            <span class="info-label">Line:</span>
            <span class="info-value">{$line}</span>
            <span class="info-label">Code:</span>
            <span class="info-value">{$statusCode}</span>
        </div>
        
        <div class="section-title">Stack Trace</div>
        <div class="trace">
HTML;
        
        foreach ($exception->getTrace() as $i => $trace) {
            $traceFile = $this->escape($trace['file'] ?? '[internal]');
            $traceLine = $trace['line'] ?? 0;
            $traceClass = $this->escape($trace['class'] ?? '');
            $traceType = $trace['type'] ?? '';
            $traceFunction = $this->escape($trace['function'] ?? '');
            
            echo "<div class='trace-item'>";
            echo "<span class='trace-num'>#{$i}</span>";
            echo "<div>";
            echo "<span class='trace-file'>{$traceFile}</span>";
            echo ":<span class='trace-line'>{$traceLine}</span><br>";
            echo "<span class='trace-func'>{$traceClass}{$traceType}{$traceFunction}()</span>";
            echo "</div>";
            echo "</div>";
        }
        
        echo "</div></div></body></html>";
    }

    /**
     * Lấy HTTP status code từ exception
     * 
     * @param Throwable $exception
     * @return int
     */
    protected function getHttpStatusCode(Throwable $exception): int
    {
        // League Route exceptions (NotFoundException, MethodNotAllowedException, etc.)
        if ($exception instanceof \League\Route\Http\Exception\HttpExceptionInterface) {
            return $exception->getStatusCode();
        }
        
        // App's own HttpException
        if ($exception instanceof \App\Core\Http\HttpException) {
            return $exception->getStatusCode();
        }
        
        // Kiểm tra exception code có phải HTTP status code hợp lệ không
        $code = $exception->getCode();
        if ($code >= 400 && $code < 600) {
            return $code;
        }
        
        return 500;
    }

    /**
     * Lấy title cho HTTP status code
     * 
     * @param int $statusCode
     * @return string
     */
    protected function getErrorTitle(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            418 => "I'm a Teapot",
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'Error'
        };
    }

    /**
     * Log error vào file
     * 
     * @param string $message
     * @param string $file
     * @param int $line
     * @param int $severity
     */
    protected function logError(string $message, string $file, int $line, int $severity): void
    {
        if (!$this->writeLogs) {
            return;
        }
        $this->ensureLogDirectory();
        
        $logFile = $this->basePath . '/logs/errors.log';
        $date = date('Y-m-d H:i:s');
        $severityName = $this->getSeverityName($severity);
        
        $log = "[{$date}] [{$severityName}] {$message} in {$file}:{$line}" . PHP_EOL;
        
        error_log($log, 3, $logFile);
    }

    /**
     * Log exception vào file
     * 
     * @param Throwable $exception
     */
    protected function logException(Throwable $exception): void
    {
        if (!$this->writeLogs) {
            return;
        }
        $this->ensureLogDirectory();
        
        $logFile = $this->basePath . '/logs/errors.log';
        $date = date('Y-m-d H:i:s');
        $class = get_class($exception);
        
        $log = "[{$date}] [EXCEPTION] {$class}: {$exception->getMessage()}" . PHP_EOL;
        $log .= "  File: {$exception->getFile()}:{$exception->getLine()}" . PHP_EOL;
        $log .= "  Trace:" . PHP_EOL;
        
        foreach (explode("\n", $exception->getTraceAsString()) as $traceLine) {
            $log .= "    {$traceLine}" . PHP_EOL;
        }
        
        $log .= PHP_EOL;
        
        error_log($log, 3, $logFile);
    }

    /**
     * Đảm bảo thư mục logs tồn tại
     */
    protected function ensureLogDirectory(): void
    {
        $logDir = $this->basePath . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Lấy tên severity từ constant
     * 
     * @param int $severity
     * @return string
     */
    protected function getSeverityName(int $severity): string
    {
        return match($severity) {
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
            default => 'UNKNOWN'
        };
    }

    /**
     * Escape HTML để tránh XSS
     * 
     * @param string $string
     * @return string
     */
    protected function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Kiểm tra có đang ở debug mode không
     * 
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Lấy tên environment hiện tại
     * 
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}