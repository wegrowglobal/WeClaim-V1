<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Clear existing data
        \App\Models\ClaimLocation::truncate();
        \App\Models\ClaimDocument::truncate();
        \App\Models\Claim::truncate();
        User::truncate();
        Role::truncate();
        Department::truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        // Run seeders
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            ClaimSeeder::class,
        ]);

        $adminRoleId = Role::where('name', 'SU')->value('id');
        $staffRoleId = Role::where('name', 'Staff')->value('id');
        $allDepartmentId = Department::where('name', 'All')->value('id');
        $randomDepartmentId = Department::where('name', '!=', 'All')->inRandomOrder()->value('id');

        if (is_null($allDepartmentId)) {
            throw new \Exception('Department "All" not found in the departments table.');
        }

        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@localhost'],
            [
                'first_name' => 'Admin',
                'second_name' => 'Admin',
                'password' => bcrypt('iCt@123./'),
                'role_id' => $adminRoleId,
                'department_id' => $allDepartmentId,
            ]
        );

        // Create test user if not exists
        User::firstOrCreate(
            ['email' => 'ammar@wegrow-global.com'],
            [
                'first_name' => 'Ammar',
                'second_name' => 'Hafiy',
                'password' => bcrypt('080808'),
                'role_id' => $staffRoleId,
                'department_id' => $randomDepartmentId,
            ]
        );
    }
}
