<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('checklist_template_id')
                ->constrained('checklist_templates')
                ->cascadeOnDelete();

            // Stable identifier for mapping answers across revisions (optional but useful).
            $table->string('key')->nullable();

            $table->string('label');
            $table->text('help_text')->nullable();

            $table->string('type', 30)->index();
            $table->boolean('is_required')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);

            // For select questions, store options (array of {value,label}) and other constraints.
            $table->json('options')->nullable();
            $table->json('validation')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->index(['checklist_template_id', 'sort_order']);
            $table->unique(['checklist_template_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_questions');
    }
};

