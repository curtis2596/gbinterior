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
        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_bill_id');
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->double('rate', 8, 2);
            $table->double('qty', 8, 2);
            $table->double('igst_per', 8, 2)->default(0.00);
            $table->double('igst', 8, 2)->default(0.00);
            $table->double('sgst_per', 8, 2)->default(0.00);
            $table->double('sgst', 8, 2)->default(0.00);
            $table->double('cgst_per', 8, 2)->default(0.00);
            $table->double('cgst', 8, 2)->default(0.00);
            $table->double('amount', 10, 2);
            $table->double('return_qty', 8, 2)->default(0.00);
            $table->double('return_igst', 8, 2)->default(0.00);
            $table->double('return_sgst', 8, 2)->default(0.00);
            $table->double('return_cgst', 8, 2)->default(0.00);
            $table->double('return_amount', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('purchase_bill_id')->references('id')->on('purchase_bills')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_items');
    }
};
