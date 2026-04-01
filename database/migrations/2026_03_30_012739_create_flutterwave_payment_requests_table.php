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
        Schema::create('flutterwave_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->comment('Internal Nuage reference (FLW-<uuid>)');
            $table->string('type')->comment('charge | transfer | refund');
            $table->string('status')->default('CREATED');
            $table->decimal('amount', 18, 2);
            $table->string('currency', 10);

            // Flutterwave resource IDs (populated after API calls)
            $table->string('flw_customer_id')->nullable()->comment('cus_xxx from Flutterwave');
            $table->string('flw_payment_method_id')->nullable()->comment('pmd_xxx from Flutterwave');
            $table->string('flw_charge_id')->nullable()->comment('chg_xxx from Flutterwave');
            $table->string('flw_transfer_id')->nullable()->comment('FLW direct-transfer id');
            $table->string('flw_refund_id')->nullable()->comment('ref_xxx from Flutterwave');

            // Payment details
            $table->string('payment_channel')->nullable()->comment('card | mobile_money | bank');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_bank_code')->nullable();
            $table->string('recipient_account_number')->nullable();

            // Fee & rate tracking (mirrors YellowCard pattern)
            $table->decimal('fee_amount', 18, 6)->nullable();
            $table->string('fee_currency', 10)->nullable();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            $table->integer('attempt')->default(1);

            $table->json('metadata')->nullable()->comment('Full API response and extra context');
            $table->timestamps();

            $table->index('flw_charge_id');
            $table->index('flw_transfer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flutterwave_payment_requests');
    }
};
