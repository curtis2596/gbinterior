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
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 80);
            $table->unsignedBigInteger('party_id');
            $table->string('party_bill_no', 80);
            $table->date('bill_date');
            $table->double('amount', 10, 2)->default(0.00);
            $table->double('freight', 8, 2)->default(0.00);
            $table->double('discount_per', 8, 2)->default(0.00);
            $table->double('discount', 8, 2)->default(0.00);
            $table->double('igst', 8, 2)->default(0.00);
            $table->double('sgst', 8, 2)->default(0.00);
            $table->double('cgst', 8, 2)->default(0.00);
            $table->double('other_charge', 8, 2)->default(0.00);
            $table->string('other_charge_reason', 180)->nullable();
            $table->double('payable_amount', 12, 2)->default(0.00);
            $table->string('narration', 255)->nullable();
            $table->string('comments', 512)->nullable();
            $table->tinyInteger('with_po')->default(0); // 0 = no PO, 1 = with PO
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('party_id')->references('id')->on('parties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};
