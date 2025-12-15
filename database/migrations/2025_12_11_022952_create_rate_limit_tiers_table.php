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
        Schema::create('rate_limit_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'free', 'basic', 'premium', 'enterprise'
            $table->string('display_name'); // e.g., 'Free Plan', 'Basic Plan'
            $table->text('description')->nullable();
            
            // Rate limits (null = unlimited)
            $table->integer('requests_per_minute')->nullable();
            $table->integer('requests_per_day')->nullable();
            $table->integer('requests_per_month')->nullable();
            
            // Pricing
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Features as JSON
            $table->json('features')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limit_tiers');
    }
};
