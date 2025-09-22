<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    /**
     * Get location details from latitude and longitude using Google Maps API.
     *
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public static function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $apiKey = config('services.google_maps.key'); // Store key in config/services.php

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "{$latitude},{$longitude}",
            'key'    => $apiKey,
        ]);


        \Log::info('Google Maps API Response: ', ['response' => $response->body()]);
        if ($response->successful()) {
            $data = $response->json();
            
            if (!empty($data['results'])) {
                return $data['results'][0]; 
            }
        }

        return null; // Return null if no result
    }
}
