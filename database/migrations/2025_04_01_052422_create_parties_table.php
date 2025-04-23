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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('opening_balance')->nullable();
            $table->string('opening_balance_type', 45)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('gstin', 15)->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('fax', 50)->nullable();
            $table->string('url', 50)->nullable();
            $table->string('tin_number', 50)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('note')->nullable();
            $table->char('type', 3)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('state_id');
            $table->boolean('is_supplier')->default(0);
            $table->boolean('is_customer')->default(0);
            $table->boolean('is_job_worker')->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
