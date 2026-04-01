<?php

namespace App\Services\Flutterwave;

use RuntimeException;

/**
 * AES-256-GCM card-field encryption matching Flutterwave v4 requirements.
 *
 * Each sensitive card field is encrypted individually using the same
 * randomly generated nonce. The resulting ciphertext includes the GCM
 * authentication tag appended to the raw ciphertext, then base64-encoded
 * — identical to the JS node-forge implementation in the reference SDK.
 */
class FlutterwaveEncryption
{
    private const CIPHER    = 'aes-256-gcm';
    private const TAG_LEN   = 16;  // 128-bit GCM auth tag
    private const NONCE_LEN = 12;  // 96-bit IV as required by Flutterwave

    /**
     * Encrypt a single plaintext field.
     *
     * @param  string $plaintext  The value to encrypt (e.g. card number)
     * @param  string $keyBase64  Base64-encoded 32-byte AES key (FLW_ENCRYPTION_KEY)
     * @param  string $nonce      12-character alphanumeric nonce
     * @return string             Base64-encoded ciphertext + auth tag
     */
    public static function encryptField(string $plaintext, string $keyBase64, string $nonce): string
    {
        $key = base64_decode($keyBase64, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('FLW_ENCRYPTION_KEY must be a base64-encoded 32-byte key.');
        }

        if (strlen($nonce) !== self::NONCE_LEN) {
            throw new RuntimeException('Nonce must be exactly 12 characters.');
        }

        $tag        = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            self::TAG_LEN
        );

        if ($ciphertext === false) {
            throw new RuntimeException('AES-GCM encryption failed: ' . openssl_error_string());
        }

        // Append tag to ciphertext then base64-encode (matches node-forge output)
        return base64_encode($ciphertext . $tag);
    }

    /**
     * Generate a random 12-character alphanumeric nonce.
     */
    public static function generateNonce(): string
    {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $nonce  = '';
        $max    = strlen($chars) - 1;
        for ($i = 0; $i < self::NONCE_LEN; $i++) {
            $nonce .= $chars[random_int(0, $max)];
        }
        return $nonce;
    }

    /**
     * Encrypt all sensitive card fields.
     *
     * @param  array  $card  [number, expiry_month, expiry_year, cvv]
     * @param  string $keyBase64
     * @return array  Encrypted card payload ready for the payment-methods endpoint
     */
    public static function encryptCard(array $card, string $keyBase64): array
    {
        $nonce = self::generateNonce();

        return [
            'encrypted_card_number'   => self::encryptField((string) $card['number'],       $keyBase64, $nonce),
            'encrypted_expiry_month'  => self::encryptField((string) $card['expiry_month'],  $keyBase64, $nonce),
            'encrypted_expiry_year'   => self::encryptField((string) $card['expiry_year'],   $keyBase64, $nonce),
            'encrypted_cvv'           => self::encryptField((string) $card['cvv'],           $keyBase64, $nonce),
            'nonce'                   => $nonce,
        ];
    }
}
