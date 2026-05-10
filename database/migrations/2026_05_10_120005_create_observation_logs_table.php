<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_entity_id')->constrained('object_entities')->cascadeOnDelete();
            $table->timestamp('logged_at')->useCurrent();
            $table->text('entry');
            $table->unsignedTinyInteger('clearance_level')->default(1);
            $table->timestamps();

            $table->index(['object_entity_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_logs');
    }
};
