<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setitems', function ($table) {
            $table->id();
            $table->unsignedInteger('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->cascadeOnDelete();
            $table->foreignId('element_type_id')
                ->constrained('service_element_types')
                ->cascadeOnDelete();
            $table->nullableMorphs('content');
            $table->unsignedInteger('sort_order');
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setitems');
    }
};
