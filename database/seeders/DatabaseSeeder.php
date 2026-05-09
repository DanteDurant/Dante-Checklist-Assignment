<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        fake()->seed(1701);

        $this->call([
            RolesSeeder::class,
            UserSeeder::class,
            ChecklistTemplateSeeder::class,
            ChecklistQuestionSeeder::class,
            ChecklistInstanceSeeder::class,
            ChecklistAnswerSeeder::class,
        ]);
    }
}
