<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_plans', function ($table) {
            $table->increments('id')->unsigned();
            $table->date('date')->unique();
            $table->integer('series_id');
            $table->integer('person_id');
            $table->string('details')->nullable();
            $table->string('reading')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_plans');
    }
};