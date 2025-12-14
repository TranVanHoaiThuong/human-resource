<?php

namespace App\Core\Helpers;

/**
 * Device Detector Helper
 * 
 * Helper class để detect device và browser từ User-Agent string.
 * Tách ra từ Auth class để dễ maintain và test.
 */
class DeviceDetector
{
    /**
     * Detect device và browser từ User-Agent
     * 
     * @param string|null $userAgent User-Agent string
     * @return string Format: "Browser on Device"
     */
    public static function detect(?string $userAgent = null): string
    {
        $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $device = self::detectDevice($userAgent);
        $browser = self::detectBrowser($userAgent);
        
        return "{$browser} on {$device}";
    }

    /**
     * Detect device từ User-Agent
     * 
     * @param string $userAgent
     * @return string
     */
    protected static function detectDevice(string $userAgent): string
    {
        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows';
        }
        
        if (preg_match('/Macintosh|Mac OS/i', $userAgent)) {
            return 'macOS';
        }
        
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }
        
        if (preg_match('/iPhone/i', $userAgent)) {
            return 'iPhone';
        }
        
        if (preg_match('/iPad/i', $userAgent)) {
            return 'iPad';
        }
        
        if (preg_match('/Android/i', $userAgent)) {
            return 'Android';
        }
        
        return 'Unknown Device';
    }

    /**
     * Detect browser từ User-Agent
     * 
     * @param string $userAgent
     * @return string
     */
    protected static function detectBrowser(string $userAgent): string
    {
        // Chrome (không phải Edge)
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edge|Edg/i', $userAgent)) {
            return 'Chrome';
        }
        
        // Firefox
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }
        
        // Safari (không phải Chrome)
        if (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            return 'Safari';
        }
        
        // Edge
        if (preg_match('/Edge|Edg/i', $userAgent)) {
            return 'Edge';
        }
        
        // Opera
        if (preg_match('/Opera|OPR/i', $userAgent)) {
            return 'Opera';
        }
        
        return 'Unknown Browser';
    }
}
