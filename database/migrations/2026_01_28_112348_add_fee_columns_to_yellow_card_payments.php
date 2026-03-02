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
        Schema::table('yellow_card_payments', function (Blueprint $table) {
            // Fee tracking columns (following StartButton pattern)
            $table->decimal('fee_amount_local', 16, 2)->nullable()->after('amount');
            $table->decimal('fee_amount_usd', 16, 6)->nullable()->after('fee_amount_local');
            $table->string('service_fee_id')->nullable()->after('fee_amount_usd');
            
            // Exchange rate and original amount
            $table->decimal('exchange_rate', 16, 4)->nullable()->after('service_fee_id');
            $table->decimal('amount_usd', 16, 6)->nullable()->after('exchange_rate');
            
            // Transaction metadata for better tracking
            $table->string('provider')->nullable()->after('amount_usd');
            $table->integer('attempt')->default(1)->after('provider');
            $table->string('withdrawal_id')->nullable()->after('attempt');
            $table->string('reason')->nullable()->after('withdrawal_id');
            
            // Destination details for reporting
            $table->string('destination_account_name')->nullable()->after('reason');
            $table->string('destination_account_number')->nullable()->after('destination_account_name');
            $table->string('destination_bank_code')->nullable()->after('destination_account_number');
            $table->string('destination_bank_name')->nullable()->after('destination_bank_code');
            $table->string('destination_network_id')->nullable()->after('destination_bank_name');
            
            // Add index on yellowcard_id for faster lookups
            $table->index('yellowcard_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yellow_card_payments', function (Blueprint $table) {
            $table->dropIndex(['yellowcard_id']);
            $table->dropColumn([
                'fee_amount_local',
                'fee_amount_usd',
                'service_fee_id',
                'exchange_rate',
                'amount_usd',
                'provider',
                'attempt',
                'withdrawal_id',
                'reason',
                'destination_account_name',
                'destination_account_number',
                'destination_bank_code',
                'destination_bank_name',
                'destination_network_id',
            ]);
        });
    }
};
