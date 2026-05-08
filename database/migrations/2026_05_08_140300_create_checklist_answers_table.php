<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('checklist_instance_id')
                ->constrained('checklist_instances')
                ->cascadeOnDelete();

            $table->foreignId('checklist_question_id')
                ->constrained('checklist_questions')
                ->cascadeOnDelete();

            // Immutable, versioned answers: each submission version creates a new row per question.
            $table->unsignedInteger('version')->default(1);

            // Keep value flexible across question types.
            $table->json('value')->nullable();
            $table->boolean('is_not_applicable')->default(false)->index();
            $table->text('notes')->nullable();

            $table->timestamp('answered_at')->nullable()->index();

            $table->timestamps();

            $table->unique(['checklist_instance_id', 'checklist_question_id', 'version'], 'uniq_instance_question_version');
            $table->index(['checklist_instance_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_answers');
    }
};

