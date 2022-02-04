<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrgChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org_charts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('under_of');
            $table->integer('order');
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('org_people');
            $table->foreign('position_id')->references('id')->on('org_positions');
            $table->foreign('division_id')->references('id')->on('org_divisions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('org_charts');
    }
}
