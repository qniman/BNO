<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('object_entities', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            $table->string('slug', 64)->unique();
            $table->string('title');
            $table->string('status', 64)->default('АКТИВЕН');
            $table->string('threat_class', 16)->nullable();
            $table->string('anomaly_type', 128)->nullable();
            $table->string('containment_level', 16)->nullable();
            $table->string('stability_index', 16)->nullable();
            $table->string('psi_index', 16)->nullable();
            $table->string('amnestic_index', 16)->nullable();
            $table->string('interaction_index', 16)->nullable();
            $table->longText('description')->nullable();
            $table->longText('containment_protocol')->nullable();
            $table->longText('history')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('public_archive')->default(false);
            $table->unsignedTinyInteger('clearance_level')->default(1);
            $table->json('dynamic_state')->nullable();
            $table->timestamps();

            $table->index(['clearance_level', 'is_hidden']);
            $table->index(['public_archive', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_entities');
    }
};
