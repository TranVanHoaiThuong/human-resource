<?php

namespace App\Core;

use DateTime;
use DateTimeZone;
use Exception;

/** Helper xử lý datetime với timezone */
class DateTimeHelper
{
    private const DEFAULT_TIMEZONE = 'Asia/Ho_Chi_Minh';

    /** Convert datetime từ UTC sang timezone của user */
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
            $dt = new DateTime($datetime, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone($timezone));

            return $dt->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }

    /** Convert datetime từ user timezone sang UTC */
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

    /** Format datetime cho hiển thị */
    public static function format(
        ?string $datetime,
        ?string $userTimezone = null,
        string $format = 'd/m/Y H:i'
    ): ?string {
        return self::toUserTimezone($datetime, $userTimezone, $format);
    }

    /** Format relative time (vd: "2 giờ trước") */
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

    public static function getDefaultTimezone(): string
    {
        return self::DEFAULT_TIMEZONE;
    }
}