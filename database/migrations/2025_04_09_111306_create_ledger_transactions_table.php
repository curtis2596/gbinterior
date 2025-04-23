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
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_account_id');
            $table->unsignedBigInteger('other_account_id');
            $table->tinyInteger('is_stock_entry')->default(0);
            $table->string('voucher_type', 45);
            $table->date('voucher_date');
            $table->string('voucher_no', 80);
            $table->double('amount', 12, 3);
            $table->string('narration', 255)->nullable();

            $table->unsignedBigInteger('purchase_bill_id')->nullable();
            $table->unsignedBigInteger('purchase_return_id')->nullable();
            $table->unsignedBigInteger('sale_bill_id')->nullable();
            $table->unsignedBigInteger('sale_ship_id')->nullable();
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('manufacture_id')->nullable();

            $table->timestamps(); // created_at & updated_at
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional: foreign keys
            $table->foreign('main_account_id')->references('id')->on('ledger_accounts')->onDelete('restrict');
            $table->foreign('other_account_id')->references('id')->on('ledger_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
    }
};
