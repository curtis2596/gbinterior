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
        Schema::create('job_order_receives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("party_id");
            $table->unsignedBigInteger("job_order_id");
            $table->date("receive_date");
            $table->float("amount", 12, 3)->default(0);
            $table->string("challan_no", 80)->nullable();
            $table->string("narration", 255);
            $table->string("comments", 255)->nullable();
            $table->timestamps();
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();

            $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
            $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_receives');
    }
};
