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
        Schema::create('ledger_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('from_account_id');
            $table->unsignedBigInteger('to_account_id');
            $table->date('voucher_date');
            $table->string('voucher_no', 80);
            $table->double('amount', 12, 3);
            $table->string('bank_transaction_no', 80)->nullable();
            $table->string('narration', 255)->nullable();

            $table->boolean('is_advance_pay')->default(false);
            $table->boolean('can_edit')->default(true);

            $table->unsignedBigInteger('purchase_bill_id')->nullable();
            $table->unsignedBigInteger('purchase_return_id')->nullable();
            $table->unsignedBigInteger('sale_bill_id')->nullable();
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('manufacture_id')->nullable();

            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('from_account_id')->references('id')->on('ledger_accounts')->onDelete('restrict');
            $table->foreign('to_account_id')->references('id')->on('ledger_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_payments');
    }
};
