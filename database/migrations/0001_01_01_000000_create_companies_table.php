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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('logo', 180)->nullable();
            $table->string('address', 200);
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('gst_number', 180)->nullable();
            $table->string('phone_number', 180)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('website', 180)->nullable();
            $table->string('bank_name', 180)->nullable();
            $table->string('account_name', 180)->nullable();
            $table->string('ifsc_code', 180)->nullable();
            $table->string('account_number', 180)->nullable();
            $table->text('terms')->nullable();
            $table->string('logo_for_pdf', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
