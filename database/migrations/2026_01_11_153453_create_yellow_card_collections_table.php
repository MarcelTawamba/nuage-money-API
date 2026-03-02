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
        Schema::create('yellow_card_collections', function (Blueprint $table) {
            $table->id();
            $table->string('sequence_id')->unique();
            $table->string('yellowcard_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('amount', 16, 2);
            $table->string('currency');
            $table->string('customer_uid')->nullable();
            $table->string('channel_id')->nullable();
            $table->text('payment_url')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yellow_card_collections');
    }
};
