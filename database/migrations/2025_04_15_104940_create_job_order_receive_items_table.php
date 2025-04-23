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
        Schema::create('job_order_receive_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('job_order_receive_id');
            $table->unsignedBigInteger('job_order_item_id');
            $table->unsignedBigInteger('to_item_id');

            $table->unsignedBigInteger('receive_warehouse_id')->nullable();
            $table->unsignedBigInteger('receive_party_id')->nullable();

            $table->double('to_qty', 12, 3);

            $table->string('comments', 255)->nullable();

            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional: foreign keys
            $table->foreign('job_order_receive_id')->references('id')->on('job_order_receives')->onDelete('cascade');
            $table->foreign('job_order_item_id')->references('id')->on('job_order_items')->onDelete('restrict');
            $table->foreign('to_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('receive_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('receive_party_id')->references('id')->on('parties')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_receive_items');
    }
};
