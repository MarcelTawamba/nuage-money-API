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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->integer('wallet_id')->unsigned();
            $table->double('balance_before');
            $table->double('balance_after');
            $table->double('amount');
            $table->string('achatable_type');
            $table->integer('achatable_id')->unsigned();
            $table->boolean('refund')->default(false);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
