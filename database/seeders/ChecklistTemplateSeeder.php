<?php

namespace Database\Seeders;

use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Support\ComplianceTemplateDefinitions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $faker = fake();

        foreach (ComplianceTemplateDefinitions::templates() as $def) {
            $status = match ($def['status']) {
                'draft' => ChecklistTemplateStatus::Draft,
                'archived' => ChecklistTemplateStatus::Archived,
                default => ChecklistTemplateStatus::Published,
            };

            $publishedAt = null;
            $archivedAt = null;

            if ($status === ChecklistTemplateStatus::Published) {
                $publishedAt = Carbon::instance($faker->dateTimeBetween('-24 months', '-1 week'));
            }

            if ($status === ChecklistTemplateStatus::Archived) {
                $publishedAt = Carbon::instance($faker->dateTimeBetween('-30 months', '-12 months'));
                $archivedAt = Carbon::instance($faker->dateTimeBetween(
                    $publishedAt->clone()->addMonths(2),
                    $publishedAt->clone()->addMonths(18)
                ));
            }

            // Include trashed rows so firstOrNew matches seeded names after soft-delete archives (Docker/local parity).
            $template = ChecklistTemplate::withTrashed()->firstOrNew(['name' => $def['name']]);

            if ($template->trashed()) {
                $template->restore();
            }

            if (! $template->exists || $template->public_id === null || $template->public_id === '') {
                $template->public_id = (string) Str::ulid();
            }

            $template->fill([
                'description' => $def['description'],
                'status' => $status,
                'created_by' => $admin->id,
                'published_at' => $publishedAt,
                'archived_at' => $archivedAt,
            ]);

            $template->save();
        }
    }
}
