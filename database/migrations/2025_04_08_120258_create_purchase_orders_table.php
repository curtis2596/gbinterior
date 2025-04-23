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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 80);
            $table->date('po_date');
            $table->unsignedBigInteger('party_id');
            $table->date('expected_delivery_date')->nullable();
            $table->double('total_amount', 12, 2);
            $table->text('terms')->nullable();
            $table->text('comments')->nullable();
            $table->text('shipping_instructions')->nullable();
            $table->string('tab_name', 45)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
