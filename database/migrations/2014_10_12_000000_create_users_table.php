<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile_no');
            $table->string('password');
            $table->string('referal_code');
            $table->string('ref_by')->nullable();
            $table->string('profile_img');
            $table->integer('ptr_reward')->default(0);
            $table->integer('first_time_payment')->default(0);
            $table->integer('wallet_amount')->default(0);
            $table->integer('withdrawal_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
