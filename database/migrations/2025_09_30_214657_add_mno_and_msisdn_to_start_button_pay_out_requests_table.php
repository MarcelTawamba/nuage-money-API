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
        Schema::table('start_button_pay_out_requests', function (Blueprint $table) {
            $table->string('mno')->nullable();
            $table->string('msisdn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('start_button_pay_out_requests', function (Blueprint $table) {
            $table->dropColumn(['mno', 'msisdn']);
        });
    }
};