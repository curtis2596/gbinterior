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
        Schema::create('sale_bills', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 80);
            $table->integer('transport', 80)->nullable();
            $table->string('vehicle_no', 255)->nullable();
            $table->string('dispatch', 255)->nullable();
            $table->string('delivered', 255)->nullable();
            $table->unsignedBigInteger('party_id');
            $table->date('bill_date');
            $table->string('reference_no', 80)->nullable();

            $table->double('amount', 12, 2)->default(0.00);
            $table->double('freight', 12, 2)->default(0.00);
            $table->double('discount_per', 8, 2)->default(0.00);
            $table->double('discount', 8, 2)->default(0.00);
            $table->double('igst', 8, 2)->default(0.00);
            $table->double('sgst', 8, 2)->default(0.00);
            $table->double('cgst', 8, 2)->default(0.00);
            $table->double('other_charge', 8, 2)->default(0.00);
            $table->string('other_charge_reason', 180)->nullable();
            $table->double('receivable_amount', 12, 2)->default(0.00);
            $table->tinyInteger('with_order')->default(1);

            $table->string('narration', 255)->nullable();
            $table->string('comments', 512)->nullable();


            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional: Add foreign key constraint
            $table->foreign('party_id')->references('id')->on('parties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_bills');
    }
};
