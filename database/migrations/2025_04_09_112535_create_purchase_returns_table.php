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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->date("voucher_date");
            $table->string("voucher_no", 45);
            $table->unsignedBigInteger("purchase_bill_id");
            $table->string("refrence_no", 45)->nullable();
            $table->float("amount")->default(0);
            $table->float("igst")->default(0);
            $table->float("sgst")->default(0);
            $table->float("cgst")->default(0);
            $table->float("other_deduction")->default(0);
            $table->string("other_deduction_reason")->nullable()->default("");
            $table->float("receivable_amount", 12, 2)->default(0);
            $table->string("narration", 255)->nullable()->default("");
            $table->string("comments", 512)->nullable()->default("");
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('purchase_bill_id')->references('id')->on('purchase_bills')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
