<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees_table', function (Blueprint $table) {
            $table->id('id');
            $table->integer('country_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->string('method_class')->default("");
            $table->string("method_name")->default("");
            $table->float('fees')->default(1);
            $table->string('fee_type')->default("percentage");
            $table->string('method_type')->default("mobile");
            $table->string("type")->default("pay_in");
            $table->float('operator_fees')->default(1);
            $table->string('operator_fee_type')->default("percentage");
            $table->timestamps();


//            $table->foreign('country_id')->references('id')->on('country_availlables');
//            $table->foreign('currency_id')->references('id')->on('currencies');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fees');
    }
};
