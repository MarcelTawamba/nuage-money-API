<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the user resource into an array.
     * Only expose safe, non-sensitive data
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashId(), // Use hashed ID instead of sequential
            'name' => $this->name,
            'email' => $this->maskEmail(), // Partially mask email
            'account_type' => $this->account_type,
            'email_verified' => (bool) $this->email_verified_at,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    /**
     * Generate a hashed/obfuscated ID to prevent user enumeration
     * Returns SHA-256 hash (non-reversible)
     */
    protected function hashId(): string
    {
        return hash('sha256', $this->id . config('app.key'));
    }

    /**
     * Mask email for privacy (show first 2 chars and domain)
     * Example: john.doe@example.com -> jo***@example.com
     */
    protected function maskEmail(): string
    {
        if (!$this->email) {
            return '';
        }

        [$local, $domain] = explode('@', $this->email);
        
        if (strlen($local) <= 2) {
            return substr($local, 0, 1) . '***@' . $domain;
        }

        return substr($local, 0, 2) . '***@' . $domain;
    }
}
