<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_entity_id')->nullable()->constrained('object_entities')->nullOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('severity', 32)->nullable();
            $table->string('casualties', 128)->nullable();
            $table->date('occurred_on')->nullable();
            $table->string('location', 255)->nullable();
            $table->unsignedTinyInteger('clearance_level')->default(1);
            $table->timestamps();

            $table->index(['occurred_on', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
