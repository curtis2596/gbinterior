<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string("type", 45);
            $table->string("name", 80);            
            $table->string("mobile", 15)->nullable();
            $table->string("whatsapp_mobile", 15)->nullable();
            $table->string("email", 80)->nullable();
            $table->string("photo", 255)->nullable();
            $table->date("dob")->nullable();
            $table->date("doj")->nullable();
            $table->unsignedBigInteger("department_id");
            $table->unsignedBigInteger("designation_id");
            $table->unsignedBigInteger("current_state_id");
            $table->unsignedBigInteger("current_city_id");
            $table->unsignedBigInteger("current_address");
            $table->unsignedBigInteger("permanent_state_id");
            $table->unsignedBigInteger("permanen_city_id");
            $table->unsignedBigInteger("permanen_address");            
            $table->string("salary_type", 45)->nullable();
            $table->string("salary_payment_mode", 45)->nullable();
            $table->boolean("dont_send_sms")->default(0);
            $table->boolean("dont_send_whatsapp_msg")->default(0);
            $table->boolean("is_mobile_hide")->default(0);
            $table->boolean("is_active")->default(0);
            $table->timestamps();
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('restrict');
            $table->foreign('current_state_id')->references('id')->on('states')->onDelete('restrict');
            $table->foreign('current_city_id')->references('id')->on('states')->onDelete('restrict');
            $table->foreign('permanent_state_id')->references('id')->on('states')->onDelete('restrict');
            $table->foreign('permanen_city_id')->references('id')->on('cities')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
