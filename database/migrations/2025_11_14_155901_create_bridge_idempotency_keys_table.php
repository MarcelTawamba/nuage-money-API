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
        Schema::create('bridge_idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->string('request_type');
            $table->text('request_payload')->nullable();
            $table->text('response_data')->nullable();
            $table->string('status')->default('pending');
            $table->integer('http_status_code')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index('request_type');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bridge_idempotency_keys');
    }
};
