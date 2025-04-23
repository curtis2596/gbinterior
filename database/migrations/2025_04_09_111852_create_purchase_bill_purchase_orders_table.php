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
        Schema::create('purchase_bill_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("purchase_bill_id");
            $table->unsignedBigInteger("purchase_order_id");
            $table->timestamps();

            $table->foreign('purchase_bill_id')->references('id')->on('purchase_bills')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_purchase_orders');
    }
};
