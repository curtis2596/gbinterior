<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable("lead_items")) {
            Schema::create('lead_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->float('qty')->nullable();
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
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
        Schema::dropIfExists('lead_items');
    }
}
