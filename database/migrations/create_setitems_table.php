<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setitems', function ($table) {
            $table->id();
            $table->integer('service_id');
            $table->integer('content_id')->nullable();
            $table->string('content_type')->nullable();
            $table->integer('sort_order');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('extra')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setitems');
    }
};
