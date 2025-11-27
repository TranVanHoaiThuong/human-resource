<?php

namespace App\Core\Http;

use Exception;
use Throwable;

/**
 * HTTP Exception
 * 
 * Exception class để throw các HTTP errors với status code cụ thể.
 * 
 * @example
 * throw new HttpException(404, 'Không tìm thấy trang');
 * throw HttpException::notFound('User không tồn tại');
 * throw HttpException::forbidden('Bạn không có quyền truy cập');
 */
class HttpException extends Exception
{
    protected int $statusCode;

    /**
     * Constructor
     * 
     * @param int $statusCode HTTP status code
     * @param string $message Error message
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        int $statusCode = 500,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        $this->statusCode = $statusCode;
        
        if (empty($message)) {
            $message = $this->getDefaultMessage($statusCode);
        }
        
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Lấy HTTP status code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Lấy default message cho status code
     * 
     * @param int $statusCode
     * @return string
     */
    protected function getDefaultMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'HTTP Error'
        };
    }

    // ============ Static Factory Methods ============

    public static function badRequest(string $message = 'Bad Request'): self
    {
        return new self(400, $message);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(401, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(403, $message);
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return new self(404, $message);
    }

    public static function methodNotAllowed(string $message = 'Method Not Allowed'): self
    {
        return new self(405, $message);
    }

    public static function unprocessableEntity(string $message = 'Unprocessable Entity'): self
    {
        return new self(422, $message);
    }

    public static function serverError(string $message = 'Internal Server Error'): self
    {
        return new self(500, $message);
    }

    public static function serviceUnavailable(string $message = 'Service Unavailable'): self
    {
        return new self(503, $message);
    }
}