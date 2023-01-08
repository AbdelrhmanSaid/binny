<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;

class PrayerTimings
{
    /**
     * PrayerTimings constructor.
     *
     * @param  string  $city
     * @param  string  $country
     */
    public function __construct(
        protected string $city,
        protected string $country
    ) {
        //
    }

    /**
     * Get the prayer timings for the given location.
     *
     * @return array|bool
     */
    public function all(): array|bool
    {
        $response = Http::get('http://api.aladhan.com/v1/timingsByCity', [
            'city' => $this->city,
            'country' => $this->country,
        ]);

        if (! $response->ok()) {
            return false;
        }

        return $response->json()['data']['timings'];
    }

    /**
     * Get only the required prayer timings.
     *
     * @return array|bool
     */
    public function required(): array|bool
    {
        $required = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        $timings = $this->all();

        if (! $timings) {
            return false;
        }

        return collect($timings)->only($required)->toArray();
    }
}
