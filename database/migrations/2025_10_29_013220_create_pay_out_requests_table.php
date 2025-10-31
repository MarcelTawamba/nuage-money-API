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
        Schema::create('pay_out_requests', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('bank_code')->nullable();
            $table->string('account_number');
            $table->string('account_name');
            $table->string('mno')->nullable();
            $table->string('msisdn')->nullable();
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
        Schema::dropIfExists('pay_out_requests');
    }
};
