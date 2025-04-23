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
        Schema::create('item_conversions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('process_id')->nullable();

            $table->unsignedBigInteger('from_item_id');
            $table->double('from_qty', 12, 3);

            $table->double('wastage_qty', 12, 3)->nullable();

            $table->unsignedBigInteger('to_item_id');
            $table->double('to_qty', 12, 3);

            $table->string('comments', 255)->nullable();

            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optionally add foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('from_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('to_item_id')->references('id')->on('items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_conversions');
    }
};
