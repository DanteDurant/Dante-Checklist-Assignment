<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password';

        $adminSeeds = [
            ['name' => 'Admin User', 'email' => 'admin@example.com'],
            ['name' => 'Priya Nandakumar', 'email' => 'priya.nandakumar@acme-compliance.example'],
            ['name' => 'Jordan Blake', 'email' => 'jordan.blake@acme-compliance.example'],
            ['name' => 'Morgan Ellis', 'email' => 'morgan.ellis@acme-compliance.example'],
        ];

        foreach ($adminSeeds as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['admin']);
        }

        $auditorSeeds = [
            ['name' => 'Auditor User', 'email' => 'auditor@example.com'],
            ['name' => 'Emily Zhao', 'email' => 'emily.zhao@fieldaudit.example'],
            ['name' => 'Diego Alvarez', 'email' => 'diego.alvarez@fieldaudit.example'],
            ['name' => 'Sofia Martins', 'email' => 'sofia.martins@fieldaudit.example'],
            ['name' => 'Noah Friedman', 'email' => 'noah.friedman@fieldaudit.example'],
            ['name' => 'Taylor Brooks', 'email' => 'taylor.brooks@fieldaudit.example'],
            ['name' => 'Riley Nguyen', 'email' => 'riley.nguyen@fieldaudit.example'],
            ['name' => 'Casey Park', 'email' => 'casey.park@fieldaudit.example'],
            ['name' => 'Avery Singh', 'email' => 'avery.singh@fieldaudit.example'],
            ['name' => 'Jamie Okafor', 'email' => 'jamie.okafor@fieldaudit.example'],
            ['name' => 'Jordan Meyer', 'email' => 'jordan.meyer@fieldaudit.example'],
            ['name' => 'Quinn Harper', 'email' => 'quinn.harper@fieldaudit.example'],
            ['name' => 'Reese Okonkwo', 'email' => 'reese.okonkwo@fieldaudit.example'],
            ['name' => 'Skylar Chen', 'email' => 'skylar.chen@fieldaudit.example'],
            ['name' => 'Parker Dubois', 'email' => 'parker.dubois@fieldaudit.example'],
        ];

        foreach ($auditorSeeds as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['auditor']);
        }
    }
}
