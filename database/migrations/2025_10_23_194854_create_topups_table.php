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
        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('transaction_type');
            $table->string('status');
            $table->decimal('fee_amount', 16, 2)->nullable();
            $table->string('merchant_id');
            $table->string('transaction_reference');
            $table->string('customer_email');
            $table->string('payment_partner_id')->nullable();
            $table->string('dva_account_number')->nullable();
            $table->decimal('amount', 16, 2);
            $table->decimal('initial_amount', 16, 2)->nullable();
            $table->string('currency');
            $table->string('narration')->nullable();
            $table->string('authorization_code')->nullable();
            $table->json('payer_information')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
