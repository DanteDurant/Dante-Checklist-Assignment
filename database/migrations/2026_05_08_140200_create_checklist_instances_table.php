<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_instances', function (Blueprint $table) {
            $table->id();

            $table->ulid('public_id')->unique();

            $table->foreignId('checklist_template_id')
                ->constrained('checklist_templates')
                ->restrictOnDelete();

            // The auditor completing the checklist.
            $table->foreignId('auditor_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('status', 20)->default('draft')->index();

            // Versioning: answers are appended by version; this points to the latest version.
            $table->unsignedInteger('current_version')->default(1);

            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('finalized_at')->nullable()->index();

            $table->timestamps();

            $table->index(['auditor_id', 'status']);
            $table->index(['checklist_template_id', 'status']);
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_instances');
    }
};

