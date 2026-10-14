<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the admin panel account from ADMIN_EMAIL / ADMIN_PASSWORD, or updates its password to match .env.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        ['name' => $name, 'email' => $email, 'password' => $password] = config('auth.default_admin');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD are not set in .env — no admin user created.');

            return;
        }

        if (strlen($password) < 8) {
            $this->command?->error('ADMIN_PASSWORD must be at least 8 characters — no admin user created.');

            return;
        }

        User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $password]);

        $this->command?->info("Admin user {$email} is ready.");
    }
}
