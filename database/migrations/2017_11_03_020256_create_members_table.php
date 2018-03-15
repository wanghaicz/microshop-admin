<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mobile', 11)->unique();
            $table->string('name', 100);
            $table->string('password', 100);
            $table->string('api_token', 100)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('member_addr', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned();
            $table->string('postcode', 6);
            $table->string('town', 30);
            $table->string('address', 255);

            // $table->foreign('member_id')
            //     ->references('id')
            //     ->on('members')
            //     ->onDelete('cascade');
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
        Schema::dropIfExists('members');
        Schema::dropIfExists('member_addr');
    }
}
