<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get role IDs
        $adminRoleId = Role::where('name', 'Admin')->value('id');
        $hrRoleId = Role::where('name', 'HR')->value('id');
        $financeRoleId = Role::where('name', 'Finance')->value('id');
        $staffRoleId = Role::where('name', 'Staff')->value('id');
        $suRoleId = Role::where('name', 'SU')->value('id');
        $managerRoleId = Role::where('name', 'Manager')->value('id');

        // Get department IDs
        $allDepartmentId = Department::where('name', 'All')->value('id');
        $departments = Department::where('name', '!=', 'All')->pluck('id')->toArray();

        // Create SU user with ID 1
        User::create([
            'id' => 1,
            'first_name' => 'Super',
            'second_name' => 'Admin',
            'email' => 'system@wegrow-global.com',
            'password' => Hash::make('iCt@123./'),
            'role_id' => $suRoleId,
            'department_id' => $allDepartmentId,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'phone' => $faker->phoneNumber(),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'zip_code' => $faker->postcode(),
            'country' => $faker->country(),
        ]);

        // Create Admin users
        User::create([
            'first_name' => $faker->firstName(),
            'second_name' => $faker->lastName(),
            'email' => "admin@wegrow-global.com",
            'password' => Hash::make('password123'),
            'role_id' => $adminRoleId,
            'department_id' => $allDepartmentId,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'phone' => $faker->phoneNumber(),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'zip_code' => $faker->postcode(),
            'country' => $faker->country(),
        ]);

        // Create Manager users
        User::create([
            'first_name' => $faker->firstName(),
            'second_name' => $faker->lastName(),
            'email' => "manager@wegrow-global.com",
            'password' => Hash::make('password123'),
            'role_id' => $managerRoleId,
            'department_id' => $allDepartmentId,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'phone' => $faker->phoneNumber(),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'zip_code' => $faker->postcode(),
            'country' => $faker->country(),
        ]);

        // Create HR users
        User::create([
            'first_name' => $faker->firstName(),
            'second_name' => $faker->lastName(),
            'email' => "hr@wegrow-global.com",
            'password' => Hash::make('password123'),
            'role_id' => $hrRoleId,
            'department_id' => $allDepartmentId,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'phone' => $faker->phoneNumber(),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'zip_code' => $faker->postcode(),
            'country' => $faker->country(),
        ]);

        // Create Finance users
        User::create([
            'first_name' => $faker->firstName(),
            'second_name' => $faker->lastName(),
            'email' => "finance@wegrow-global.com",
            'password' => Hash::make('password123'),
            'role_id' => $financeRoleId,
            'department_id' => $allDepartmentId,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'phone' => $faker->phoneNumber(),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'zip_code' => $faker->postcode(),
            'country' => $faker->country(),
        ]);

        // Create Staff users (10-15)
        for ($i = 0; $i < rand(10, 15); $i++) {
            User::create([
                'first_name' => $faker->firstName(),
                'second_name' => $faker->lastName(),
                'email' => "staff{$i}@wegrow-global.com",
                'password' => Hash::make('password123'),
                'role_id' => $staffRoleId,
                'department_id' => $faker->randomElement($departments),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'zip_code' => $faker->postcode(),
                'country' => $faker->country(),
            ]);
        }
    }
}
