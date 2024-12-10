<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{

    //////////////////////////////////////////////////////////////////

    public function run()
    {
        $roles = [
            ['id' => 1, 'name' => 'Staff'],
            ['id' => 2, 'name' => 'Admin'],
            ['id' => 3, 'name' => 'HR'],
            ['id' => 4, 'name' => 'Finance'],
            ['id' => 5, 'name' => 'SU']
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']],
                $role
            );
        }
    }

    //////////////////////////////////////////////////////////////////

}
