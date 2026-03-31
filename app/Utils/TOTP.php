<?php

namespace App\Utils;

class TOTP
{
    /**
     * Base32 encoding table.
     */
    private static $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a new base32 secret.
     *
     * @param int $length
     * @return string
     */
    public static function generateSecret($length = 16)
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate a provisioning URL for Google Authenticator.
     *
     * @param string $name App name
     * @param string $user User email or name
     * @param string $secret The base32 secret
     * @return string
     */
    public static function getProvisioningUrl($name, $user, $secret)
    {
        $name = urlencode($name);
        $user = urlencode($user);
        return "otpauth://totp/{$name}:{$user}?secret={$secret}&issuer={$name}";
    }

    /**
     * Get the current 6-digit TOTP code for a given secret.
     *
     * @param string $secret
     * @param int|null $timeSlot
     * @return string
     */
    public static function getCode($secret, $timeSlot = null)
    {
        if ($timeSlot === null) {
            $timeSlot = floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);

        // Pack time into 64-bit string
        $time = pack('N*', 0) . pack('N*', $timeSlot);

        // Generate HMAC-SHA1
        $hash = hash_hmac('sha1', $time, $secretKey, true);

        // Extract 4-byte dynamic offset
        $offset = ord(substr($hash, -1)) & 0x0F;
        $fourBytes = substr($hash, $offset, 4);

        // Convert to integer, drop highest bit
        $value = unpack('N', $fourBytes)[1] & 0x7FFFFFFF;

        // Modulo 10^6 for 6 digits
        $code = $value % 1000000;

        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a code against a secret. Allows a window of 1 time slot (30 seconds) before or after.
     *
     * @param string $secret
     * @param string $code
     * @return bool
     */
    public static function verify($secret, $code)
    {
        $currentTimeSlot = floor(time() / 30);

        for ($i = -1; $i <= 1; $i++) {
            if (self::getCode($secret, $currentTimeSlot + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decode a base32 string.
     *
     * @param string $base32
     * @return string
     */
    private static function base32Decode($base32)
    {
        if (empty($base32)) return '';

        $base32 = strtoupper($base32);
        $l = strlen($base32);
        $n = 0;
        $j = 0;
        $binary = '';

        for ($i = 0; $i < $l; $i++) {
            $n = $n << 5;
            $n = $n + strpos(self::$base32chars, $base32[$i]);
            $j += 5;

            if ($j >= 8) {
                $j -= 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }
}
