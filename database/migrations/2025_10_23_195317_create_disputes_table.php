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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference');
            $table->string('dispute_reference')->unique();
            $table->string('currency');
            $table->decimal('amount', 16, 2);
            $table->string('elapse_time')->nullable();
            $table->string('type');
            $table->string('customer_email');
            $table->string('status');
            $table->string('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
