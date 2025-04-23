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
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('process_id');

            $table->string('order_no', 80);
            $table->date('expected_complete_date');
            $table->double('amount', 12, 3)->default(0);

            $table->string('comments', 255)->nullable();
            $table->string('will_receive_at_type', 45);

            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Optional foreign keys:
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
