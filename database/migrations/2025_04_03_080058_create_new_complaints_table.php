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
        Schema::create('new_complaints', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('complaint_no')->unique();
            $table->string('contact_number');
            $table->string('contact_person');
            $table->string('status', 45);
            $table->boolean('is_new_party')->nullable();
            $table->boolean('is_free')->default(0);
            $table->unsignedBigInteger('assign_to');
            $table->float('amount')->nullable();
            $table->float('sale_bill_no')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->foreignId('party_id')->constrained('parties')->onDelete('cascade');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_complaints');
    }
};
