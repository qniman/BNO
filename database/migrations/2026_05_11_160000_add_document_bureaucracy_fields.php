<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('revision_label', 16)->default('1.0')->after('published_at');
            $table->string('stamp', 64)->nullable()->after('revision_label');
            $table->string('registration_number', 128)->nullable()->after('stamp');
            $table->text('routing_note')->nullable()->after('registration_number');
            $table->string('acknowledgment_stub', 255)->nullable()->after('routing_note');
            $table->text('marginalia')->nullable()->after('acknowledgment_stub');
            $table->unsignedTinyInteger('corruption_level')->default(0)->after('marginalia');
            $table->boolean('chronology_anomaly')->default(false)->after('corruption_level');
            $table->boolean('leaked_publish')->default(false)->after('chronology_anomaly');
            $table->string('references_object_slug', 64)->nullable()->after('leaked_publish');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'revision_label',
                'stamp',
                'registration_number',
                'routing_note',
                'acknowledgment_stub',
                'marginalia',
                'corruption_level',
                'chronology_anomaly',
                'leaked_publish',
                'references_object_slug',
            ]);
        });
    }
};
