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
        Schema::create('job_order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('job_order_id');
            $table->unsignedBigInteger('from_item_id');
            $table->double('from_qty', 10, 3);

            $table->unsignedBigInteger('to_item_id')->nullable();
            $table->double('to_qty', 10, 3)->default(0);

            $table->string('comments', 255)->nullable();

            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional foreign keys
            $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
            $table->foreign('from_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('to_item_id')->references('id')->on('items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_items');
    }
};
