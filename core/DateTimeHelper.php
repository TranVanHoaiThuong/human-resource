<?php

namespace App\Core;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * DateTime Helper
 * 
 * Xử lý convert datetime theo timezone của user
 */
class DateTimeHelper
{
    /**
     * Default timezone (TPHCM)
     */
    private const DEFAULT_TIMEZONE = 'Asia/Ho_Chi_Minh';

    /**
     * Convert datetime từ database (UTC) sang timezone của user
     * 
     * @param string|null $datetime Datetime string từ database (UTC)
     * @param string|null $userTimezone Timezone của user (vd: 'Asia/Ho_Chi_Minh')
     * @param string $format Format output (default: 'Y-m-d H:i:s')
     * @return string|null
     */
    public static function toUserTimezone(
        ?string $datetime,
        ?string $userTimezone = null,
        string $format = 'Y-m-d H:i:s'
    ): ?string {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $userTimezone ?? self::DEFAULT_TIMEZONE;

        try {
            // Tạo DateTime từ string (assume UTC từ database)
            $dt = new DateTime($datetime, new DateTimeZone('UTC'));
            
            // Convert sang timezone của user
            $dt->setTimezone(new DateTimeZone($timezone));
            
            return $dt->format($format);
        } catch (Exception $e) {
            // Fallback: return original nếu có lỗi
            return $datetime;
        }
    }

    /**
     * Convert datetime từ user timezone sang UTC để lưu database
     * 
     * @param string $datetime Datetime string từ user input
     * @param string|null $userTimezone Timezone của user
     * @return string|null
     */
    public static function toUTC(
        string $datetime,
        ?string $userTimezone = null
    ): ?string {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $userTimezone ?? self::DEFAULT_TIMEZONE;

        try {
            $dt = new DateTime($datetime, new DateTimeZone($timezone));
            $dt->setTimezone(new DateTimeZone('UTC'));
            
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Format datetime với format đẹp cho hiển thị
     * 
     * @param string|null $datetime
     * @param string|null $userTimezone
     * @param string $format Format (vd: 'd/m/Y H:i', 'd/m/Y', 'H:i')
     * @return string|null
     */
    public static function format(
        ?string $datetime,
        ?string $userTimezone = null,
        string $format = 'd/m/Y H:i'
    ): ?string {
        return self::toUserTimezone($datetime, $userTimezone, $format);
    }

    /**
     * Format relative time (vd: "2 giờ trước", "hôm qua")
     * 
     * @param string|null $datetime
     * @param string|null $userTimezone
     * @return string|null
     */
    public static function relative(
        ?string $datetime,
        ?string $userTimezone = null
    ): ?string {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $userTimezone ?? self::DEFAULT_TIMEZONE;

        try {
            $dt = new DateTime($datetime, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone($timezone));
            
            $now = new DateTime('now', new DateTimeZone($timezone));
            $diff = $now->diff($dt);
            
            if ($diff->days > 7) {
                return $dt->format('d/m/Y');
            } elseif ($diff->days > 0) {
                return $diff->days . ' ngày trước';
            } elseif ($diff->h > 0) {
                return $diff->h . ' giờ trước';
            } elseif ($diff->i > 0) {
                return $diff->i . ' phút trước';
            } else {
                return 'Vừa xong';
            }
        } catch (Exception $e) {
            return $datetime;
        }
    }

    /**
     * Lấy timezone mặc định
     * 
     * @return string
     */
    public static function getDefaultTimezone(): string
    {
        return self::DEFAULT_TIMEZONE;
    }
}