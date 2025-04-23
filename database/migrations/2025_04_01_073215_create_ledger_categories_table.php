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
        Schema::create('ledger_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['liability', 'asset', 'contra']);
            $table->string('name', 45);
            $table->string('code', 45)->nullable();
            $table->boolean('is_pre_defined')->default(1);
            $table->timestamps();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_categories');
    }
};
