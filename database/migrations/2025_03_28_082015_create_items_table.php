<?php

use Illuminate\Container\Attributes\DB;
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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_category_id')->nullable();
            $table->unsignedBigInteger('item_group_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name', 180);
            $table->string('specification', 255)->nullable();
            $table->string('sku', 255)->unique();
            $table->string('user_id', 180)->nullable();
            $table->string('company_name', 180)->nullable();
            $table->string('master_id', 180)->nullable();
            $table->string('part_number', 180)->nullable();
            $table->string('group', 180)->nullable();
            $table->string('item_alias', 180)->nullable();
            $table->string('category', 180)->nullable();
            $table->string('hsn_code', 180)->nullable();
            $table->boolean('is_finished_item')->default(0);
            $table->double('purchase_rate', 10, 3)->default(0);
            $table->double('sale_rate', 10, 3)->default(0);
            $table->double('job_worker_rate', 10, 3)->default(0);
            $table->decimal('tax_rate', 8, 3)->nullable();
            $table->boolean('is_active')->default(1);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Foreign keys (Optional: Uncomment if relationships exist)
            $table->foreign('item_category_id')->references('id')->on('item_categories')->onDelete('restrict');
            $table->foreign('item_group_id')->references('id')->on('item_groups')->onDelete('restrict');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
