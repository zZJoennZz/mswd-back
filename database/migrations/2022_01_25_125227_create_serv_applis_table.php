<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServApplisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serv_applis', function (Blueprint $table) {
            $table->id();
            $table->text('application_id');
            $table->unsignedBigInteger('service_id');
            $table->text('first_name');
            $table->text('middle_name');
            $table->text('last_name');
            $table->date('birthday');
            $table->date('gender');
            $table->date('email_address');
            $table->integer('contact_number');
            $table->integer('status')->default('0');
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serv_applis');
    }
}
