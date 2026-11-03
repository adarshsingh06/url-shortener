<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower((string) env('SUPERADMIN_EMAIL', 'superadmin@example.com'));
        $password = (string) env('SUPERADMIN_PASSWORD', 'ChangeMe!12345');

        if (DB::table('users')->where('email', $email)->exists()) {
            $this->command?->warn("SuperAdmin {$email} already exists; seeder left it unchanged.");
            return;
        }

        // The assignment asks for raw SQL for the initial SuperAdmin account.
        DB::insert(
            'INSERT INTO users (company_id, name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [null, 'Super Admin', $email, Hash::make($password), 'SuperAdmin', now(), now()]
        );

        $this->command?->info("Created SuperAdmin: {$email}");
        $this->command?->warn('Change the configured seed password before using this outside local development.');
    }
}
