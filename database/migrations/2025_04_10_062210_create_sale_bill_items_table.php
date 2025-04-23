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
        Schema::create('sale_bill_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_bill_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('sale_order_item_id')->nullable();

            $table->double('rate', 8, 2);
            $table->double('qty', 8, 2);

            $table->double('igst_per', 8, 2)->default(0.00);
            $table->double('igst', 8, 2)->default(0.00);
            $table->double('sgst_per', 8, 2)->default(0.00);
            $table->double('sgst', 8, 2)->default(0.00);
            $table->double('cgst_per', 8, 2)->default(0.00);
            $table->double('cgst', 8, 2)->default(0.00);

            $table->double('amount', 10, 2)->default(0.00);

            $table->double('return_qty', 8, 2)->default(0.00);
            $table->double('return_igst', 8, 2)->default(0.00);
            $table->double('return_sgst', 8, 2)->default(0.00);
            $table->double('return_cgst', 8, 2)->default(0.00);
            $table->double('return_amount', 10, 2)->default(0.00);

            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optionally, add foreign key constraints
            $table->foreign('sale_bill_id')->references('id')->on('sale_bills')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('sale_order_item_id')->references('id')->on('sale_order_items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_bill_items');
    }
};
