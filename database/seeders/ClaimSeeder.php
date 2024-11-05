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
    private $malaysianCities = [
        ['name' => 'Kuala Lumpur', 'lat' => 3.1390, 'lng' => 101.6869],
        ['name' => 'Johor Bahru', 'lat' => 1.4927, 'lng' => 103.7414],
        ['name' => 'Penang', 'lat' => 5.4141, 'lng' => 100.3288],
        ['name' => 'Kota Kinabalu', 'lat' => 5.9804, 'lng' => 116.0735],
    ];

    private function calculateDistance($city1, $city2)
    {
        $earthRadius = 6371; // km

        $lat1 = deg2rad($city1['lat']);
        $lng1 = deg2rad($city1['lng']);
        $lat2 = deg2rad($city2['lat']);
        $lng2 = deg2rad($city2['lng']);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlng/2) * sin($dlng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function run(): void
    {
        $faker = Faker::create('ms_MY');
        $staffRoleId = 1;
        $departments = Department::where('name', '!=', 'All')->pluck('id')->toArray();

        foreach (range(1, 10) as $i) {
            $user = User::create([
                'first_name' => $faker->firstName,
                'second_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'role_id' => $staffRoleId,
                'department_id' => $faker->randomElement($departments),
            ]);

            foreach (range(1, $faker->numberBetween(1, 5)) as $j) {
                // Generate random route with 2-5 locations
                $locationCount = $faker->numberBetween(2, 5);
                $route = array_map(function($index) use ($faker) {
                    return $faker->randomElement($this->malaysianCities);
                }, range(1, $locationCount));

                // Calculate total distance
                $totalDistance = 0;
                $distances = [];
                for ($k = 0; $k < count($route) - 1; $k++) {
                    $distance = $this->calculateDistance($route[$k], $route[$k + 1]);
                    $distances[] = $distance;
                    $totalDistance += $distance;
                }

                // Create claim
                $claim = Claim::create([
                    'user_id' => $user->id,
                    'title' => 'Petrol Claim - ' . strtoupper($faker->company),
                    'description' => $faker->sentence(),
                    'petrol_amount' => $totalDistance * 0.6,
                    'status' => $faker->randomElement(['Submitted', 'Approved_Admin', 'Approved_HR', 'Done']),
                    'claim_type' => 'Petrol',
                    'total_distance' => $totalDistance,
                    'submitted_at' => now(),
                    'claim_company' => strtoupper($faker->company),
                    'toll_amount' => $faker->randomFloat(2, 10, 100),
                    'from_location' => $route[0]['name'],
                    'to_location' => end($route)['name'],
                    'date_from' => now(),
                    'date_to' => now()->addDays($faker->numberBetween(1, 5)),
                    'token' => $faker->uuid,
                ]);

                // Create locations with distances
                foreach ($route as $index => $city) {
                    ClaimLocation::create([
                        'claim_id' => $claim->id,
                        'location' => $city['name'],
                        'distance' => $distances[$index - 1] ?? null,
                        'order' => $index + 1,
                    ]);
                }

                // Create documents
                ClaimDocument::create([
                    'claim_id' => $claim->id,
                    'toll_file_name' => 'toll_receipt.pdf',
                    'toll_file_path' => 'claims/documents/toll.pdf',
                    'email_file_name' => 'email_confirmation.pdf',
                    'email_file_path' => 'claims/documents/email.pdf',
                    'uploaded_by' => $user->id,
                ]);
            }
        }
    }
}
