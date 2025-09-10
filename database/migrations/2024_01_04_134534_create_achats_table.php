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
        Schema::create('achats', function (Blueprint $table) {
            $table->id('id');
            $table->string('client_id');
            $table->string('country');
            $table->double('amount');
            $table->string('currency');
            $table->string('ref_id');
            $table->string('requestable_type');
            $table->integer('requestable_id')->unsigned();
            $table->string('user_ref_id');
            $table->string('status')->default("CREATED");
            $table->integer("job_tries")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('achats');
    }
};
