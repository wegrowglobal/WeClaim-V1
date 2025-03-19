<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{

    //////////////////////////////////////////////////////////////////

    public function run()
    {
        $departments = [
            'Administration',
            'Human Resources',
            'Finance and Account',
            'Marketing',
            'Sales',
            'IT and Technical',
            'Procurement and Assets',
            'Retails',
            'All'
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['name' => $department],
                ['name' => $department]
            );
        }
        
    }

    //////////////////////////////////////////////////////////////////
}
