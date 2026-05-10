<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->string('codename')->unique();
            $table->string('real_name')->nullable();
            $table->string('rank', 128)->nullable();
            $table->string('division', 8)->nullable();
            $table->string('profile_type', 64)->nullable();
            $table->unsignedTinyInteger('clearance_level')->default(1);
            $table->string('status', 64)->default('действующий');
            $table->longText('biography')->nullable();
            $table->boolean('deceased')->default(false);
            $table->timestamps();

            $table->index(['division', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};
