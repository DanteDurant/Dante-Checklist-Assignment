<?php

namespace Database\Seeders;

use App\Enums\ChecklistInstanceStatus;
use App\Enums\ChecklistQuestionType;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $auditor = User::where('email', 'auditor@example.com')->first();

        if (!$admin || !$auditor) {
            return;
        }

        $template = ChecklistTemplate::firstOrCreate(
            ['name' => 'ISO 27001 Starter Checklist'],
            [
                'public_id' => (string) Str::ulid(),
                'description' => 'Seeded demo checklist template.',
                'status' => ChecklistTemplateStatus::Published,
                'created_by' => $admin->id,
                'published_at' => now(),
            ]
        );

        $q1 = ChecklistQuestion::firstOrCreate(
            ['checklist_template_id' => $template->id, 'key' => 'access_control_policy'],
            [
                'label' => 'Do you have an access control policy?',
                'type' => ChecklistQuestionType::Boolean,
                'is_required' => true,
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        $q2 = ChecklistQuestion::firstOrCreate(
            ['checklist_template_id' => $template->id, 'key' => 'risk_assessment_date'],
            [
                'label' => 'Date of last risk assessment',
                'type' => ChecklistQuestionType::Date,
                'is_required' => false,
                'sort_order' => 20,
                'is_active' => true,
            ]
        );

        $instance = ChecklistInstance::firstOrCreate(
            ['auditor_id' => $auditor->id, 'checklist_template_id' => $template->id],
            [
                'public_id' => (string) Str::ulid(),
                'status' => ChecklistInstanceStatus::InProgress,
                'current_version' => 1,
                'started_at' => now()->subHour(),
            ]
        );

        ChecklistAnswer::firstOrCreate(
            ['checklist_instance_id' => $instance->id, 'checklist_question_id' => $q1->id, 'version' => 1],
            [
                'value' => ['boolean' => true],
                'answered_at' => now()->subMinutes(30),
            ]
        );

        ChecklistAnswer::firstOrCreate(
            ['checklist_instance_id' => $instance->id, 'checklist_question_id' => $q2->id, 'version' => 1],
            [
                'value' => ['date' => now()->subDays(30)->toDateString()],
                'answered_at' => now()->subMinutes(25),
            ]
        );
    }
}

