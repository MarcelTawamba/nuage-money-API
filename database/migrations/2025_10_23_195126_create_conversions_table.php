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
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('transaction_type');
            $table->string('status');
            $table->decimal('from_amount', 16, 2)->nullable();
            $table->decimal('to_amount', 16, 2)->nullable();
            $table->string('from_currency')->nullable();
            $table->string('to_currency')->nullable();
            $table->string('merchant_id');
            $table->string('transaction_reference');
            $table->decimal('amount', 16, 2);
            $table->string('currency');
            $table->decimal('fee_amount', 16, 2)->nullable();
            $table->string('authorization_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};
