<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('name_plural');
            $table->string('url_prefix')->unique();
            $table->string('layout')->default('article');
            $table->text('description')->nullable();
            $table->boolean('numbered')->default(false);
            $table->string('number_format')->nullable();
            $table->json('fields')->nullable();
            $table->boolean('show_in_nav')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_type_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->unsignedInteger('number')->nullable();
            $table->text('summary')->nullable();
            $table->json('fields')->nullable();
            $table->json('content')->nullable();
            $table->longText('content_text')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['page_type_id', 'slug']);
            $table->index(['page_type_id', 'number']);
            $table->index(['status', 'published_at']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['page_id', 'tag_id']);
        });

        // Ссылки между страницами: из полей типа «страница» и из текста. Пересобираются при сохранении.
        Schema::create('page_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('to_page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('source');
            $table->index('to_page_id');
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('fields')->nullable();
            $table->json('content')->nullable();
            $table->json('tags')->nullable();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('page_links');
        Schema::dropIfExists('page_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_types');
    }
};
