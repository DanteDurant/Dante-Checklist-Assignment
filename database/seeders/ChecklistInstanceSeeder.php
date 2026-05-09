<?php

namespace Database\Seeders;

use App\Enums\ChecklistInstanceStatus;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChecklistInstanceSeeder extends Seeder
{
    public function run(): void
    {
        $auditors = User::role('auditor')->get();

        if ($auditors->isEmpty()) {
            return;
        }

        $templates = ChecklistTemplate::query()
            ->where('status', ChecklistTemplateStatus::Published)
            ->get();

        if ($templates->isEmpty()) {
            return;
        }

        $faker = fake();

        for ($i = 0; $i < 155; $i++) {
            /** @var User $auditor */
            $auditor = $auditors->random();
            /** @var ChecklistTemplate $template */
            $template = $templates->random();

            $roll = $faker->numberBetween(1, 100);

            if ($roll <= 54) {
                $status = ChecklistInstanceStatus::Submitted;
                $submittedAt = Carbon::instance($faker->dateTimeBetween('-540 days', 'now'));
                $startedAt = (clone $submittedAt)->subHours($faker->numberBetween(6, 360));
                $finalizedAt = null;
            } elseif ($roll <= 76) {
                $status = ChecklistInstanceStatus::Approved;
                $submittedAt = Carbon::instance($faker->dateTimeBetween('-540 days', 'now'));
                $startedAt = (clone $submittedAt)->subHours($faker->numberBetween(6, 360));
                $finalizedAt = $faker->boolean(70)
                    ? (clone $submittedAt)->addHours($faker->numberBetween(2, 96))
                    : null;
            } elseif ($roll <= 88) {
                $status = ChecklistInstanceStatus::InProgress;
                $startedAt = Carbon::instance($faker->dateTimeBetween('-120 days', 'now'));
                $submittedAt = null;
                $finalizedAt = null;
            } elseif ($roll <= 95) {
                $status = ChecklistInstanceStatus::Draft;
                $startedAt = $faker->boolean(40)
                    ? Carbon::instance($faker->dateTimeBetween('-60 days', 'now'))
                    : null;
                $submittedAt = null;
                $finalizedAt = null;
            } else {
                $status = ChecklistInstanceStatus::Rejected;
                $startedAt = Carbon::instance($faker->dateTimeBetween('-300 days', '-1 day'));
                $submittedAt = null;
                $finalizedAt = null;
            }

            ChecklistInstance::create([
                'public_id' => (string) Str::ulid(),
                'checklist_template_id' => $template->id,
                'auditor_id' => $auditor->id,
                'status' => $status,
                'current_version' => 1,
                'started_at' => $startedAt,
                'submitted_at' => $submittedAt,
                'finalized_at' => $finalizedAt,
            ]);
        }
    }
}
