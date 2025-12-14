<?php

namespace App\Core\Auth;

use Doctrine\DBAL\Connection;
use App\Core\Logging\Logger;
use App\Core\Helpers\DeviceDetector;

class Auth
{
    protected Connection $db;
    protected ?Logger $logger;
    protected ?array $user = null;
    protected ?string $currentToken = null;
    protected bool $userLoaded = false;
    
    // Cookie settings
    protected string $cookieName = 'auth_token';
    protected int $sessionLifetime = 86400 * 7; // 7 days
    protected int $rememberLifetime = 86400 * 30; // 30 days

    public function __construct(Connection $db, ?Logger $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->loadUserFromCookie();
    }

    /**
     * Attempt to authenticate user
     */
    public function attempt(string $username, string $password, bool $remember = false): bool
    {
        $user = $this->db->fetchAssociative(
            'SELECT * FROM users WHERE username = ? AND status = ?',
            [$username, 'active']
        );

        if (!$user || !password_verify($password, $user['password'])) {
            if ($this->logger) {
                $this->logger->warning('Login attempt failed', [
                    'username' => $username,
                    'ip' => $this->getClientIp(),
                ]);
            }
            return false;
        }

        // Create session
        $token = $this->createSession($user['id'], $remember);
        $this->setAuthCookie($token, $remember);
        
        $this->user = $user;
        $this->currentToken = $token;
        
        if ($this->logger) {
            $this->logger->info('User logged in', [
                'user_id' => $user['id'],
                'username' => $username,
                'ip' => $this->getClientIp(),
            ]);
        }
        
        return true;
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Check if user is guest
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get current authenticated user
     */
    public function user(): ?array
    {
        if (!$this->userLoaded) {
            $this->loadUserFromCookie();
        }
        return $this->user;
    }

    /**
     * Get current user ID
     */
    public function id(): ?int
    {
        $user = $this->user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Logout current device
     */
    public function logout(): void
    {
        if ($this->currentToken) {
            $this->db->delete('user_sessions', ['token' => $this->currentToken]);
        }
        
        $this->clearAuthCookie();
        $this->user = null;
        $this->currentToken = null;
    }

    /**
     * Logout all devices for current user
     */
    public function logoutAll(): void
    {
        if ($userId = $this->id()) {
            $this->db->delete('user_sessions', ['user_id' => $userId]);
        }
        
        $this->clearAuthCookie();
        $this->user = null;
        $this->currentToken = null;
    }

    /**
     * Logout other devices (keep current)
     */
    public function logoutOtherDevices(): int
    {
        if (!$userId = $this->id()) {
            return 0;
        }

        return (int) $this->db->executeStatement(
            'DELETE FROM user_sessions WHERE user_id = ? AND token != ?',
            [$userId, $this->currentToken]
        );
    }

    /**
     * Get all sessions for current user
     */
    public function sessions(): array
    {
        if (!$userId = $this->id()) {
            return [];
        }

        $sessions = $this->db->fetchAllAssociative(
            'SELECT id, token, device_info, ip_address, user_agent, last_activity, created_at 
             FROM user_sessions 
             WHERE user_id = ? AND expires_at > NOW()
             ORDER BY last_activity DESC',
            [$userId]
        );

        // Mark current session
        foreach ($sessions as &$session) {
            $session['is_current'] = $session['token'] === $this->currentToken;
            // Hide full token, show only last 8 chars
            $session['token_display'] = '...' . substr($session['token'], -8);
        }

        return $sessions;
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(int $sessionId): bool
    {
        if (!$userId = $this->id()) {
            return false;
        }

        $affected = $this->db->delete('user_sessions', [
            'id' => $sessionId,
            'user_id' => $userId
        ]);

        return $affected > 0;
    }

    /**
     * Update last activity timestamp
     */
    public function touchSession(): void
    {
        if ($this->currentToken) {
            $this->db->update(
                'user_sessions',
                ['last_activity' => new \DateTimeImmutable()],
                ['token' => $this->currentToken],
                ['last_activity' => 'datetime_immutable']
            );
        }
    }

    /**
     * Clean up expired sessions (call via cron)
     */
    public function cleanupExpiredSessions(): int
    {
        return (int) $this->db->executeStatement(
            'DELETE FROM user_sessions WHERE expires_at < NOW()'
        );
    }

    // ========== Protected Methods ==========

    protected function loadUserFromCookie(): void
    {
        $this->userLoaded = true;
        
        $token = $_COOKIE[$this->cookieName] ?? null;
        if (!$token) {
            return;
        }

        $session = $this->db->fetchAssociative(
            'SELECT * FROM user_sessions WHERE token = ? AND expires_at > NOW()',
            [$token]
        );

        if (!$session) {
            $this->clearAuthCookie();
            return;
        }

        $user = $this->db->fetchAssociative(
            'SELECT * FROM users WHERE id = ? AND status = ?',
            [$session['user_id'], 'active']
        );

        if (!$user) {
            $this->db->delete('user_sessions', ['token' => $token]);
            $this->clearAuthCookie();
            return;
        }

        $this->user = $user;
        $this->currentToken = $token;
        
        // Update last activity (mỗi 5 phút)
        $lastActivity = new \DateTimeImmutable($session['last_activity']);
        if ($lastActivity->diff(new \DateTimeImmutable())->i >= 5) {
            $this->touchSession();
        }
    }

    protected function createSession(int $userId, bool $remember = false): string
    {
        $token = bin2hex(random_bytes(32)); // 64 chars
        $lifetime = $remember ? $this->rememberLifetime : $this->sessionLifetime;
        $expiresAt = new \DateTimeImmutable("+{$lifetime} seconds");

        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'token' => $token,
            'device_info' => $this->getDeviceInfo(),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => $expiresAt,
            'last_activity' => new \DateTimeImmutable(),
            'created_at' => new \DateTimeImmutable(),
        ], [
            'expires_at' => 'datetime_immutable',
            'last_activity' => 'datetime_immutable',
            'created_at' => 'datetime_immutable',
        ]);

        return $token;
    }

    protected function setAuthCookie(string $token, bool $remember = false): void
    {
        $lifetime = $remember ? $this->rememberLifetime : $this->sessionLifetime;
        $expires = time() + $lifetime;
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        
        setcookie($this->cookieName, $token, [
            'expires' => $expires,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    protected function clearAuthCookie(): void
    {
        setcookie($this->cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[$this->cookieName]);
    }

    protected function getClientIp(): ?string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = explode(',', $_SERVER[$header])[0];
                return trim($ip);
            }
        }
        
        return null;
    }

    protected function getDeviceInfo(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        return DeviceDetector::detect($userAgent);
    }
}