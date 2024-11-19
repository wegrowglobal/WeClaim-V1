<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\User;
use App\Models\Role;
use App\Models\ClaimLocation;
use App\Models\ClaimDocument;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClaimSeeder extends Seeder
{
    private $malaysianLocations = [
        'Kuala Lumpur',
        'Petaling Jaya',
        'Shah Alam',
        'Klang',
        'Subang Jaya',
        'Puchong',
        'Cyberjaya',
        'Putrajaya',
        'Seremban',
        'Melaka',
        'Johor Bahru',
        'Penang',
        'Ipoh',
        'Kuantan',
        'Kota Bharu'
    ];

    public function run(): void
    {
        // Get staff users
        $staffUsers = User::whereHas('role', function($query) {
            $query->where('name', 'Staff');
        })->get();

        // Get initial reviewer (Admin)
        $adminReviewer = User::whereHas('role', function($query) {
            $query->where('name', 'Admin');
        })->first();

        // Generate 25-50 claims
        $numberOfClaims = rand(25, 50);

        for ($i = 0; $i < $numberOfClaims; $i++) {
            $dateFrom = Carbon::now()->subDays(rand(1, 30));
            $dateTo = (clone $dateFrom)->addDays(rand(1, 5));
            $totalDistance = rand(50, 500);
            $petrolAmount = $totalDistance * 0.6;
            
            $claim = Claim::create([
                'user_id' => $staffUsers->random()->id,
                'reviewer_id' => $adminReviewer->id,
                'title' => 'Petrol Claim - ' . fake()->randomElement(['WGG', 'WGE', 'WGG & WGE']),
                'description' => fake()->sentence(),
                'claim_company' => fake()->randomElement(['WGG', 'WGE', 'WGG & WGE']),
                'petrol_amount' => $petrolAmount,
                'toll_amount' => rand(0, 50),
                'total_distance' => $totalDistance,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => fake()->randomElement([
                    Claim::STATUS_SUBMITTED,
                    Claim::STATUS_APPROVED_ADMIN,
                    Claim::STATUS_APPROVED_DATUK,
                    Claim::STATUS_APPROVED_HR,
                    Claim::STATUS_APPROVED_FINANCE,
                    Claim::STATUS_REJECTED,
                    Claim::STATUS_DONE
                ]),
                'claim_type' => 'Petrol',
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            // Create document records with fake paths
            ClaimDocument::create([
                'claim_id' => $claim->id,
                'toll_file_name' => 'toll_receipt_' . $claim->id . '.pdf',
                'toll_file_path' => 'claims/toll/toll_receipt_' . $claim->id . '.pdf',
                'email_file_name' => 'email_approval_' . $claim->id . '.pdf',
                'email_file_path' => 'claims/email/email_approval_' . $claim->id . '.pdf',
                'uploaded_by' => $claim->user_id
            ]);

            // Create 2-5 locations for each claim
            $numberOfLocations = rand(2, 5);
            $usedLocations = [];
            $previousLocation = $this->malaysianLocations[array_rand($this->malaysianLocations)];
            $usedLocations[] = $previousLocation;

            for ($j = 0; $j < $numberOfLocations; $j++) {
                // Get a random location that hasn't been used in this claim
                do {
                    $nextLocation = $this->malaysianLocations[array_rand($this->malaysianLocations)];
                } while (in_array($nextLocation, $usedLocations));
                
                $usedLocations[] = $nextLocation;
                
                ClaimLocation::create([
                    'claim_id' => $claim->id,
                    'from_location' => $previousLocation,
                    'to_location' => $nextLocation,
                    'distance' => rand(10, 100),
                    'order' => $j + 1
                ]);

                $previousLocation = $nextLocation;
            }
        }
    }
} 