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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('transaction_type');
            $table->string('status');
            $table->decimal('fee_amount', 16, 2)->nullable();
            $table->string('merchant_id');
            $table->string('transaction_reference');
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 16, 2);
            $table->string('currency');
            $table->json('recipient')->nullable();
            $table->string('authorization_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
