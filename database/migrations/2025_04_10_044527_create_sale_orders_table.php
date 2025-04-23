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
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 80);
            $table->string('party_order_no', 80)->nullable();
            $table->unsignedBigInteger('party_id');
            $table->date('order_date');
            $table->date('expected_delivery_date');
            $table->double('total_amount', 12, 2);
            $table->text('shipping_instructions')->nullable();
            $table->string('comments', 255)->nullable();

            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional foreign key constraint
            $table->foreign('party_id')->references('id')->on('parties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_orders');
    }
};
