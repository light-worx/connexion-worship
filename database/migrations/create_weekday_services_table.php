<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('weekday_services', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('type')->default('fixed');
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->integer('offset')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekday_services');
    }
};