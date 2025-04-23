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
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45)->nullable();
            $table->enum('type', ['simple', 'bank', 'party', 'employee'])->default('simple');
            $table->string('name', 100)->nullable();
            $table->unsignedBigInteger('ledger_category_id')->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->string('opening_balance_type', 45)->nullable();
            $table->decimal('current_balance', 12, 2)->default(0.00);
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_branch_name', 120)->nullable();
            $table->string('bank_branch_address', 120)->nullable();
            $table->string('bank_branch_ifsc', 30)->nullable();
            $table->string('bank_account_no', 30)->nullable();
            $table->string('comments', 255)->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_pre_defined')->default(0);
            $table->boolean('can_delete')->default(1);
            $table->boolean('allow_pay_for_end_user')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Foreign key constraints
            $table->foreign('ledger_category_id')->references('id')->on('ledger_categories')->onDelete('restrict');
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
