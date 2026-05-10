<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('object_entity_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_entity_id')->constrained('object_entities')->cascadeOnDelete();
            $table->foreignId('related_object_entity_id')->constrained('object_entities')->cascadeOnDelete();
            $table->string('relation_type', 64)->default('связь');
            $table->unsignedTinyInteger('clearance_level')->default(1);
            $table->timestamps();

            $table->unique(['object_entity_id', 'related_object_entity_id', 'relation_type'], 'object_link_unique');
            $table->index(['object_entity_id', 'clearance_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_entity_links');
    }
};
