<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User\User;
use App\Models\Auth\Role;
use App\Models\User\Department;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create storage directories if they don't exist
        if (!file_exists(storage_path('app/public/claims/toll'))) {
            mkdir(storage_path('app/public/claims/toll'), 0755, true);
        }
        if (!file_exists(storage_path('app/public/claims/email'))) {
            mkdir(storage_path('app/public/claims/email'), 0755, true);
        }

        // Create a dummy PDF file to copy
        $dummyPdfContent = '%PDF-1.4' . PHP_EOL . '%%EOF';
        file_put_contents(storage_path('app/public/dummy.pdf'), $dummyPdfContent);

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
            UserSeeder::class,
        ]);

        // Create the symbolic link if it doesn't exist
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

    }
}
