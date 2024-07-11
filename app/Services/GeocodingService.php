<?php

namespace App\Services;

class GeocodingService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('google.api_key');
    }

    public function getCoordinates($address)
    {
        if($address != null){
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $this->apiKey;
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if ($data['status'] === 'OK') {
                $location = $data['results'][0]['geometry']['location'];
                return [
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                ];
            }
        }

        return null;
    }
}
