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
        if (Schema::hasTable('lc_regions') && !Schema::hasColumn('lc_regions', 'is_visible')) {
            Schema::table('lc_regions', function (Blueprint $table) {
                $table->boolean('is_visible')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('lc_regions') && Schema::hasColumn('lc_regions', 'is_visible')) {
            Schema::table('lc_regions', function (Blueprint $table) {
                $table->dropColumn('is_visible');
            });
        }
    }
};
