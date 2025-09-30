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
        Schema::table('start_button_pay_in_requests', function (Blueprint $table) {
            $table->string('redirect_url')->nullable();
            $table->string('webhook_url')->nullable();
            $table->json('payment_methods')->nullable();
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('start_button_pay_in_requests', function (Blueprint $table) {
            $table->dropColumn(['redirect_url', 'webhook_url', 'payment_methods', 'metadata']);
        });
    }
};