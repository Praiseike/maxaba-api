<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LocationIQService
{
    /**
     * Get location details from latitude and longitude using Location IQ Maps API.
     *
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public static function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $apiKey = config('services.location_iq.key'); 

        $response = Http::get('https://us1.locationiq.com/v1/reverse', [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json',
            'key'    => $apiKey,
        ]);


        \Log::info('Location IQ API Response: ', ['response' => $response->body()]);
        if ($response->successful()) {
            $data = $response->json();
            
           return $data;
        }

        return null;
    }
}
