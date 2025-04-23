<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsers11feb2025A extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {            
            $table->json("used_in_other_table_created_by")->nullable()->after("created_by");
            $table->json("used_in_other_table_updated_by")->nullable()->after("updated_by");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('used_in_other_table_created_by');
            $table->dropColumn('used_in_other_table_updated_by');
        });
    }
}
