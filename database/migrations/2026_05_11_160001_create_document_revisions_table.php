<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('revision_label', 16);
            $table->timestamp('revised_at')->useCurrent();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_reason')->nullable();
            $table->longText('content_snapshot')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'revised_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_revisions');
    }
};
