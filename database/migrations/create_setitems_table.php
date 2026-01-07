<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setitems', function ($table) {
            $table->increments('id')->unsigned();
            $table->foreignId('service_plan_id')->nullable();
            $table->foreignId('service_id')->nullable();
            $table->integer('content_id')->nullable();
            $table->string('content_type')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->timestamps();
            $table->index(['service_plan_id', 'sort_order']);
            $table->index(['service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setitems');
    }
};
