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
        Schema::create('sale_bill_item_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("sale_bill_item_id");
            $table->unsignedBigInteger("warehouse_id");
            $table->float("qty");
            $table->timestamps();

            $table->foreign('sale_bill_item_id')->references('id')->on('sale_bill_items')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_bill_item_warehouses');
    }
};
