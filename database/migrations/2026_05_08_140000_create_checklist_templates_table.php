<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();

            // Recommendation: expose ULIDs publicly in APIs, keep bigint PKs for joins/perf.
            $table->ulid('public_id')->unique();

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('status', 20)->default('draft')->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();

            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};

