<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSqlLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sql_logs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string("route_name_or_url")->nullable();
            $table->string("sql_log_file")->nullable();
            $table->string("sql_dml_log_file")->nullable();
            $table->boolean("have_dml_query")->nullable();
            $table->boolean("have_heavy_query")->nullable();
            $table->timestamps();
            $table->bigInteger("created_by")->nullable();
            $table->index("route_name_or_url");
            $table->index("created_by");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sql_logs');
    }
}
