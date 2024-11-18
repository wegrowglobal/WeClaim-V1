<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BankingInstitutionService
{
    private const API_URL = 'https://api.bnm.gov.my/public/banking/institutions';
    private const CACHE_KEY = 'malaysian_banks';
    private const CACHE_DURATION = 86400; // 24 hours

    public function getBankingInstitutions()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                $response = Http::get(self::API_URL);
                
                if ($response->successful()) {
                    $banks = collect($response->json('data'))
                        ->where('institution_type', 'BANK')
                        ->sortBy('name')
                        ->pluck('name')
                        ->toArray();
                    
                    Log::info('Successfully fetched banks from BNM API', [
                        'count' => count($banks)
                    ]);
                    
                    return $banks;
                }
            } catch (\Exception $e) {
                Log::error('Error fetching banks from BNM API', [
                    'error' => $e->getMessage()
                ]);
            }
            
            // Fallback bank list if API fails
            return [
                'Maybank',
                'CIMB Bank',
                'Public Bank',
                'RHB Bank',
                'Hong Leong Bank',
                'AmBank',
                'Bank Islam Malaysia',
                'Bank Rakyat',
                'OCBC Bank Malaysia',
                'UOB Malaysia'
            ];
        });
    }
} 