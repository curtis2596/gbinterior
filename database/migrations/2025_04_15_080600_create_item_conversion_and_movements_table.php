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
        Schema::create('item_conversion_and_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');

            $table->unsignedBigInteger('from_item_id');
            $table->unsignedBigInteger('to_item_id');

            $table->string('challan_no', 80);
            $table->date('challan_date');

            $table->double('from_qty', 12, 3);
            $table->double('wastage_qty', 12, 3)->nullable();
            $table->double('to_qty', 12, 3);

            $table->unsignedBigInteger('process_id');

            $table->double('amount', 12, 3)->nullable();

            $table->string('comments', 255)->nullable();

            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional: Add foreign keys
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('from_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('to_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_conversion_and_movements');
    }
};
