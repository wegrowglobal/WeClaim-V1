<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Claim;
use App\Models\Department;
use App\Models\ClaimDocument;
use App\Models\ClaimLocation;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ClaimSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ms_MY');
        $staffRoleId = 1; // Assuming staff role_id is 1
        $departments = Department::where('name', '!=', 'All')->pluck('id')->toArray();

        $malaysianCities = [
            ['name' => 'Kuala Lumpur', 'lat' => 3.1390, 'lng' => 101.6869],
            ['name' => 'Johor Bahru', 'lat' => 1.4927, 'lng' => 103.7414],
            ['name' => 'Penang', 'lat' => 5.4141, 'lng' => 100.3288],
            ['name' => 'Kota Kinabalu', 'lat' => 5.9804, 'lng' => 116.0735],
        ];

        // Create 20 staff users
        for ($i = 0; $i < 10; $i++) {
            $user = User::create([
                'first_name' => $faker->firstName,
                'second_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'role_id' => $staffRoleId,
                'department_id' => $faker->randomElement($departments),
            ]);

            // Create 1-5 claims for each user
            $claimCount = $faker->numberBetween(1, 5);
            for ($j = 0; $j < $claimCount; $j++) {
                $fromCity = $faker->randomElement($malaysianCities);
                $toCity = $faker->randomElement($malaysianCities);
                while ($fromCity === $toCity) {
                    $toCity = $faker->randomElement($malaysianCities);
                }

                $dateFrom = $faker->dateTimeBetween('-3 months', 'now');
                $dateTo = (clone $dateFrom)->modify('+' . $faker->numberBetween(1, 5) . ' days');

                $purposes = [
                    'Client meeting and project discussion',
                    'Training session and workshop',
                    'Site inspection and evaluation',
                    'Sales presentation and negotiation',
                    'Conference and networking event',
                    'Project kickoff meeting',
                    'Technical consultation',
                    'Contract signing ceremony',
                    'Product demonstration',
                    'Team building activity'
                ];

                $additionalDetails = [
                    'with potential investors',
                    'with regional partners',
                    'for upcoming project phase',
                    'regarding system implementation',
                    'for business expansion',
                    'with stakeholders',
                    'for quarterly review',
                    'with international clients',
                    'for market research',
                    'with local authorities'
                ];

                $purpose = $faker->randomElement($purposes);
                $detail = $faker->randomElement($additionalDetails);

                $claim = Claim::create([
                    'user_id' => $user->id,
                    'title' => ucfirst($purpose) . ' in ' . $toCity['name'],
                    'description' => $purpose . ' ' . $detail . ' in ' . $toCity['name'] . '. Travel from ' . $fromCity['name'] . ' for business purposes.',
                    'petrol_amount' => $faker->randomFloat(2, 50, 500),
                    'status' => $faker->randomElement(['Submitted', 'Approved_Admin', 'Approved_Datuk', 'Approved_HR', 'Approved_Finance', 'Done', 'Rejected']),
                    'claim_type' => 'Petrol', 
                    'total_distance' => $faker->randomFloat(2, 50, 1000),
                    'submitted_at' => Carbon::now(),
                    'claim_company' => $faker->company,
                    'toll_amount' => $faker->randomFloat(2, 10, 100),
                    'from_location' => $fromCity['name'],
                    'to_location' => $toCity['name'],
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'token' => $faker->unique()->uuid,
                ]);

                // Create claim document
                ClaimDocument::create([
                    'claim_id' => $claim->id,
                    'toll_file_name' => 'toll_receipt_' . $faker->word . '.pdf',
                    'toll_file_path' => 'claims/documents/toll_' . $faker->uuid . '.pdf',
                    'email_file_name' => 'email_confirmation_' . $faker->word . '.pdf',
                    'email_file_path' => 'claims/documents/email_' . $faker->uuid . '.pdf',
                    'uploaded_by' => $user->id,
                ]);

                // Create 2-5 locations for each claim
                $locationCount = $faker->numberBetween(2, 5);
                $visitedCities = [$fromCity, $toCity];
                for ($l = 0; $l < $locationCount; $l++) {
                    $city = $visitedCities[$l] ?? $faker->randomElement($malaysianCities);
                    ClaimLocation::create([
                        'claim_id' => $claim->id,
                        'location' => $city['name'],
                        'order' => $l + 1,
                    ]);
                }
            }
        }
    }
}
