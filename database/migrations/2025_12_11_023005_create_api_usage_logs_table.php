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
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('api_key_id');
            $table->string('endpoint');
            $table->string('method', 10);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('request_payload_hash', 64)->nullable();
            $table->integer('response_status');
            $table->integer('response_time_ms')->nullable();
            $table->timestamp('created_at');
            
            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('cascade');
            $table->index(['api_key_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
