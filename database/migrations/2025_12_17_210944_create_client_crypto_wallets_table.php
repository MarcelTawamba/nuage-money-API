<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_crypto_wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_id'); // Links to oauth_clients (UUID type)
            $table->foreignId('crypto_asset_id')->constrained('crypto_assets')->onDelete('cascade'); // Which crypto asset
            $table->uuid('blockradar_address_id'); // BlockRadar's unique address ID
            $table->string('deposit_address'); // The actual blockchain address (0x... or base58)
            $table->decimal('balance', 36, 18)->default(0); // Current balance (high precision for crypto)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_deposit_at')->nullable();
            $table->timestamp('last_withdrawal_at')->nullable();
            $table->timestamps();
            
            // Foreign keys and Indexes
            $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('cascade');
            $table->unique(['client_id', 'crypto_asset_id']); // One wallet per client per asset
            $table->index('deposit_address');
            $table->index('blockradar_address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_crypto_wallets');
    }
};
