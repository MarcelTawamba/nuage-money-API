<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EncryptSensitiveResponse
{
    /**
     * Encrypt sensitive API responses (optional advanced security)
     * Client must decrypt using shared key/public key
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only encrypt JSON responses
        if (!$response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        }

        // Check if encryption is requested
        if (!$request->hasHeader('X-Request-Encryption')) {
            return $response;
        }

        $data = $response->getData(true);
        
        // Encrypt the data
        $encryptedData = $this->encryptData($data);

        return response()->json([
            'encrypted' => true,
            'data' => $encryptedData,
            'algorithm' => 'AES-256-CBC',
        ]);
    }

    /**
     * Encrypt data using AES-256-CBC
     */
    protected function encryptData(array $data): string
    {
        $key = config('app.key');
        $plaintext = json_encode($data);
        
        // Generate initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        
        // Encrypt
        $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, 0, $iv);
        
        // Combine IV and encrypted data
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Decrypt data (for reference - client-side implementation needed)
     */
    public static function decryptData(string $encryptedData): array
    {
        $key = config('app.key');
        $decoded = base64_decode($encryptedData);
        
        [$iv, $encrypted] = explode('::', $decoded, 2);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        
        return json_decode($decrypted, true);
    }
}
