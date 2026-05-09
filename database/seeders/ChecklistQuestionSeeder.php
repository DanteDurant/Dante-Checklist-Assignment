<?php

namespace Database\Seeders;

use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Database\Seeders\Support\ComplianceTemplateDefinitions;
use Illuminate\Database\Seeder;

class ChecklistQuestionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ComplianceTemplateDefinitions::templates() as $def) {
            $template = ChecklistTemplate::query()->where('name', $def['name'])->first();

            if (! $template) {
                continue;
            }

            foreach ($def['questions'] as $q) {
                ChecklistQuestion::updateOrCreate(
                    [
                        'checklist_template_id' => $template->id,
                        'key' => $q['key'],
                    ],
                    [
                        'label' => $q['label'],
                        'help_text' => $q['help_text'] ?? null,
                        'type' => $q['type'],
                        'is_required' => $q['is_required'],
                        'sort_order' => $q['sort_order'],
                        'options' => $q['options'] ?? null,
                        'validation' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
