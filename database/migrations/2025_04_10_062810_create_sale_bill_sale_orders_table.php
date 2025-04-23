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
        Schema::create('sale_bill_sale_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("sale_bill_id");
            $table->unsignedBigInteger("sale_order_id");
            $table->timestamps();
            
            $table->foreign('sale_bill_id')->references('id')->on('sale_bills')->onDelete('cascade');
            $table->foreign('sale_order_id')->references('id')->on('sale_orders')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_bill_sale_orders');
    }
};
