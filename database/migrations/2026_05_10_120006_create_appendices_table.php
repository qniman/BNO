<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appendices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_entity_id')->constrained('object_entities')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->longText('body');
            $table->unsignedTinyInteger('clearance_level')->default(2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('corrupted')->default(false);
            $table->timestamps();

            $table->index(['object_entity_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appendices');
    }
};
