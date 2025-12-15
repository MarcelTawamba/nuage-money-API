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
        Schema::create('api_key_scopes', function (Blueprint $table) {
            $table->id();
            $table->uuid('api_key_id');
            $table->foreignId('scope_id')->constrained('api_scopes')->onDelete('cascade');
            $table->timestamps();
            
            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('cascade');
            $table->unique(['api_key_id', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_key_scopes');
    }
};
