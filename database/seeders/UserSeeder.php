<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get role IDs
        $adminRoleId = Role::where('name', 'Admin')->value('id');
        $hrRoleId = Role::where('name', 'HR')->value('id');
        $financeRoleId = Role::where('name', 'Finance')->value('id');
        $staffRoleId = Role::where('name', 'Staff')->value('id');
        $suRoleId = Role::where('name', 'SU')->value('id');

        // Get department IDs
        $allDepartmentId = Department::where('name', 'All')->value('id');
        $departments = Department::where('name', '!=', 'All')->pluck('id')->toArray();

        // Create SU user with ID 1
        User::create([
            'id' => 1,
            'first_name' => 'Super',
            'second_name' => 'Admin',
            'email' => 'admin@localhost',
            'password' => Hash::make('iCt@123./'),
            'role_id' => $suRoleId,
            'department_id' => $allDepartmentId,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'zip_code' => fake()->postcode(),
            'country' => fake()->country(),
        ]);

        // Reserve IDs 2-10 for future special users
        for ($i = 2; $i <= 10; $i++) {
            User::create([
                'id' => $i,
                'first_name' => 'Reserved',
                'second_name' => 'User ' . $i,
                'email' => "reserved{$i}@example.com",
                'password' => Hash::make('password123'),
                'role_id' => $adminRoleId,
                'department_id' => $allDepartmentId,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip_code' => fake()->postcode(),
                'country' => fake()->country(),
            ]);
        }

        // Create Admin users (2-3) starting from ID 11
        for ($i = 0; $i < rand(2, 3); $i++) {
            User::create([
                'first_name' => fake()->firstName(),
                'second_name' => fake()->lastName(),
                'email' => "admin{$i}@example.com",
                'password' => Hash::make('password123'),
                'role_id' => $adminRoleId,
                'department_id' => $allDepartmentId,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip_code' => fake()->postcode(),
                'country' => fake()->country(),
            ]);
        }

        // Create HR users (2-3)
        for ($i = 0; $i < rand(2, 3); $i++) {
            User::create([
                'first_name' => fake()->firstName(),
                'second_name' => fake()->lastName(),
                'email' => "hr{$i}@example.com",
                'password' => Hash::make('password123'),
                'role_id' => $hrRoleId,
                'department_id' => $allDepartmentId,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip_code' => fake()->postcode(),
                'country' => fake()->country(),
            ]);
        }

        // Create Finance users (2-3)
        for ($i = 0; $i < rand(2, 3); $i++) {
            User::create([
                'first_name' => fake()->firstName(),
                'second_name' => fake()->lastName(),
                'email' => "finance{$i}@example.com",
                'password' => Hash::make('password123'),
                'role_id' => $financeRoleId,
                'department_id' => $allDepartmentId,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip_code' => fake()->postcode(),
                'country' => fake()->country(),
            ]);
        }

        // Create Staff users (10-15)
        for ($i = 0; $i < rand(10, 15); $i++) {
            User::create([
                'first_name' => fake()->firstName(),
                'second_name' => fake()->lastName(),
                'email' => "staff{$i}@example.com",
                'password' => Hash::make('password123'),
                'role_id' => $staffRoleId,
                'department_id' => fake()->randomElement($departments),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip_code' => fake()->postcode(),
                'country' => fake()->country(),
            ]);
        }
    }
} 