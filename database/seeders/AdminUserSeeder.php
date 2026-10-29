<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@example.com');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'Admin'),
                'password' => (string) env('ADMIN_PASSWORD', 'password'),
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info("Admin garantido: {$email}");
    }
}
