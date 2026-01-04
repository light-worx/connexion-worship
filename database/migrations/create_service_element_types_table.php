<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_element_types', function ($table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('expects_content')->default(false);
            $table->string('content_kind')->nullable();
            $table->timestamps();
        });

    }
    
    public function down()
    {
        Schema::dropIfExists('service_element_types');
    }
};
