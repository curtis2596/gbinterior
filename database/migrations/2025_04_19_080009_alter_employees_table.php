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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('current_address');
            $table->dropColumn('permanent_address');
            $table->dropForeign(['current_city_id']);

            $table->foreign('current_city_id')
                ->references('id')
                ->on('cities')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['current_city_id']);

            $table->foreign('current_city_id')
                ->references('id')
                ->on('cities')
                ->onDelete('restrict');
        });
    }
};
