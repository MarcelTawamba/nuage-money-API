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
        Schema::create('crypto_assets', function (Blueprint $table) {
            $table->id();
            $table->string('blockchain_name'); // e.g., 'base', 'solana', 'ethereum'
            $table->string('blockchain_slug')->index(); // e.g., 'base', 'solana'
            $table->string('asset_symbol'); // e.g., 'USDC', 'USDT', 'cNGN'
            $table->string('asset_name'); // e.g., 'USD Coin'
            $table->enum('network', ['testnet', 'mainnet'])->default('testnet');
            $table->uuid('blockradar_asset_id')->unique(); // BlockRadar's asset ID
            $table->uuid('blockradar_wallet_id'); // Nuage's master wallet ID for this chain
            $table->string('blockradar_blockchain_id'); // BlockRadar's blockchain ID
            $table->string('contract_address')->nullable(); // Token contract address (null for native tokens)
            $table->unsignedTinyInteger('decimals'); // Token decimals
            $table->string('currency', 10); // USD, NGN, EUR, etc.
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_native')->default(false); // True for ETH, SOL, etc.
            $table->boolean('is_evm_compatible')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['blockchain_slug', 'network', 'is_active']);
            $table->index('asset_symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_assets');
    }
};
