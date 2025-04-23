<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileImageToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("uid", 255)->nullable()->after("id");
            $table->string("type", 45)->nullable()->after("uid");            
            
            $table->string("mobile", 15)->nullable()->after("email_verified_at");
            $table->string("otp", 45)->nullable()->after("mobile");
            $table->dateTime("otp_sent_datetime")->nullable()->after("otp");

            $table->string('profile_image', 255)->nullable()->after('remember_token');
            $table->boolean("dont_send_email")->default(0)->after("profile_image");
            $table->boolean("is_active")->default(0)->after("dont_send_email");
            $table->boolean("is_pre_defined")->default(0)->after("is_active");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uid');
            $table->dropColumn('type');

            $table->dropColumn('mobile');
            $table->dropColumn('otp');
            $table->dropColumn('otp_sent_datetime');
            
            $table->dropColumn('profile_image');
            $table->dropColumn('dont_send_email');
            $table->dropColumn('is_active');
            $table->dropColumn('is_pre_defined');
        });
    }
}
