<?php

namespace App\Traits;

trait HasHashedId
{
    /**
     * Get hashed ID for public display (prevents user enumeration)
     * This is a non-reversible hash - use for displaying IDs publicly
     */
    public function getHashedIdAttribute(): string
    {
        return hash('sha256', $this->id . config('app.key'));
    }

    /**
     * Get short public ID (non-cryptographic, just obfuscated)
     * This is a simple alternative to hashids if you don't need to decode it
     */
    public function getPublicIdAttribute(): string
    {
        // Simple base62 encoding without external dependencies
        return $this->encodeBase62($this->id * 123456789); // Multiply by prime to obfuscate
    }

    /**
     * Decode public ID back to database ID
     * Note: This is basic obfuscation, not cryptographic security
     */
    public static function decodePublicId(string $publicId): ?int
    {
        try {
            $decoded = static::decodeBase62($publicId);
            return (int) ($decoded / 123456789); // Divide by same prime
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encode integer to base62 string
     */
    protected function encodeBase62(int $number): string
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($alphabet);
        $result = '';

        while ($number > 0) {
            $result = $alphabet[$number % $base] . $result;
            $number = (int) ($number / $base);
        }

        return $result ?: '0';
    }

    /**
     * Decode base62 string to integer
     */
    protected static function decodeBase62(string $string): int
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($alphabet);
        $result = 0;
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $result = $result * $base + strpos($alphabet, $string[$i]);
        }

        return $result;
    }
}
