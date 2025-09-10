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
        Schema::create('toupesu_payment_request', function (Blueprint $table) {
            $table->id();
            $table->string("payment_method");
            $table->string("msidn",20);
            $table->string("pay_token")->default("");
            $table->string("status")->default("CREATED");
            $table->string("reason")->default("");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toupesu_payment_request');
    }
};
