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
        Schema::create('new_quotations', function (Blueprint $table) {
            $table->id();
            $table->date('date')->notNull();
            $table->boolean('is_new')->notNull();
            $table->string('customer_name', 45)->nullable();
            $table->string('customer_email', 80)->nullable();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('status', 45)->notNull();
            $table->string('pdf', 255)->nullable();
            $table->boolean('is_email_sent')->notNull()->default(0);
            $table->foreignId('follow_up_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('follow_up_date')->nullable();
            $table->string('follow_up_type', 45)->nullable();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('new_quotations');
    }
};
