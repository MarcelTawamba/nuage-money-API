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
        Schema::create('start_button_pay_out_requests', function (Blueprint $table) {
            $table->id();
            $table->string('bank_code');
            $table->string('account_number');
            $table->string('account_name');
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
        Schema::dropIfExists('start_button_pay_out_requests');
    }
};
