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
        Schema::create('fincra_mobile_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string("client_id",40);
            $table->string("country_code",3);
            $table->string("currency_code",10);
            $table->double("amount");
            $table->string("customer_name",100)->default('');
            $table->string("customer_email",100)->default('');
            $table->string("payment_method");
            $table->string("msidn",20);
            $table->string("ref_id");
            $table->string("user_ref_id");
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
        Schema::dropIfExists('fincra_mobile_payment_requests');
    }
};
