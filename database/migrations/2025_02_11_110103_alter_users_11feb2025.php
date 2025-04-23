<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsers11feb2025 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean("can_edit")->default(1)->after("is_pre_defined");
            $table->json("cannot_edit_reason_array")->nullable()->after("can_edit");
            $table->boolean("can_delete")->default(1)->after("cannot_edit_reason_array");
            $table->json("cannot_delete_reason_array")->nullable()->after("can_delete");
            $table->unsignedBigInteger("created_by")->nullable();
            $table->unsignedBigInteger("updated_by")->nullable();
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
            $table->dropColumn('can_edit');
            $table->dropColumn('cannot_edit_reason_array');
            $table->dropColumn('can_delete');
            $table->dropColumn('cannot_delete_reason_array');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
}
