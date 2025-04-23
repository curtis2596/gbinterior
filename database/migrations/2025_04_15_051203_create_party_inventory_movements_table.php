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
        Schema::create('party_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("warehouse_id");
            $table->unsignedBigInteger("party_id");
            $table->string("challan_no", 80);
            $table->date("challan_date");
            $table->string("comments", 255)->nullable();            
            $table->timestamps();
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_inventory_movements');
    }
};
