<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 64)->default('протокол');
            $table->longText('content')->nullable();
            $table->string('classification', 64)->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('clearance_level')->default(1);
            $table->boolean('is_redacted')->default(false);
            $table->boolean('volatile')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['clearance_level', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
