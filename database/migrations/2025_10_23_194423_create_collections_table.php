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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('transaction_type');
            $table->string('status');
            $table->string('merchant_id');
            $table->string('transaction_reference')->nullable();
            $table->string('customer_email');
            $table->string('user_transaction_reference')->nullable();
            $table->string('payment_code')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->decimal('fee_amount', 16, 2)->nullable();
            $table->string('narration')->nullable();
            $table->decimal('amount', 16, 2);
            $table->string('currency');
            $table->string('authorization_code')->nullable();
            $table->json('extra_information')->nullable();
            $table->json('payer_information')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
