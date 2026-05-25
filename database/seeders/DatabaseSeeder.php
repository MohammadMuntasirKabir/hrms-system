<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create additional test user if needed
        if (! User::where('email', 'test@example.com')->exists()) {
            $company = Company::first();
            if ($company) {
                $user = User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'company_id' => $company->id,
                ]);
                $user->assignRole('employee');
            }
        }
    }
}
