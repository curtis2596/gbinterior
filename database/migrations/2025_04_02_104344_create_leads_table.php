<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable("leads")) {
            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->date('date')->notNull();
                $table->unsignedBigInteger('lead_source_id');
                $table->boolean('is_new')->notNull();
                $table->string('customer_name', 80)->nullable();
                $table->string('customer_email', 80)->nullable();
                $table->unsignedBigInteger('party_id')->nullable();
                $table->boolean('is_include_items')->nullable();
                $table->string('level', 45)->notNull();
                $table->string('status', 45)->notNull();
                $table->string('not_in_interested_reason', 255)->nullable();
                $table->unsignedBigInteger('follow_up_user_id')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->string('follow_up_type', 45)->nullable();
                $table->string('mature_action_type', 45)->nullable();
                $table->text('comments')->nullable();
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->foreign('lead_source_id')->references('id')->on('sources')->onDelete('restrict');
                $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
                $table->foreign('follow_up_user_id')->references('id')->on('users')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
}
