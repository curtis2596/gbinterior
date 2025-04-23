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
        Schema::create('party_inventory_movement_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('party_inventory_movement_id');
            $table->unsignedBigInteger('item_id');
            $table->float('qty');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Shorter foreign key names
            $table->foreign('party_inventory_movement_id', 'pim_items_pim_fk')
                ->references('id')->on('party_inventory_movements')
                ->onDelete('cascade');

            $table->foreign('item_id', 'pim_items_item_fk')
                ->references('id')->on('items')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_inventory_movement_items');
    }
};
