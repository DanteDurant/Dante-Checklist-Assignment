<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('export_type', 64);
            $table->string('status', 32);
            $table->json('filters');
            $table->boolean('is_inline')->default(false);
            $table->string('disk', 32)->default('exports');
            $table->string('relative_path')->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->string('dedupe_hash', 64)->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
