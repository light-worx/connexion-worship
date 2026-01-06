<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_plans', function ($table) {
            $table->id();

            $table->date('date')->unique();

            // Sermon planning
            $table->foreignId('series_id')
                ->nullable()
                ->constrained('series')
                ->nullOnDelete();

            $table->string('theme')->nullable();
            $table->text('notes')->nullable();

            // Preacher (external system)
            $table->unsignedBigInteger('preacher_external_id')->nullable();
            $table->string('preacher_name')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_plans');
    }
};