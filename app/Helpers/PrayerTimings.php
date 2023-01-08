<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class PrayerTimings
{
    /**
     * The city name.
     *
     * @var string
     */
    protected string $city;

    /**
     * The country name.
     *
     * @var string
     */
    protected string $country;

    /**
     * Prayer timings.
     *
     * @var Collection
     */
    protected Collection $timings;

    /**
     * Allowed prayer timings.
     *
     * @var array
     */
    const ALLOWED_PRAYERS = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];

    /**
     * PrayerTimings constructor.
     *
     * @param  string  $city
     * @param  string  $country
     */
    public function __construct(string $city, string $country)
    {
        $this->city = $city;
        $this->country = $country;

        $response = Http::get('http://api.aladhan.com/v1/timingsByCity', [
            'city' => $city,
            'country' => $country,
        ]);

        if (! $response->ok()) {
            throw new Exception('Could not get prayer times for the given location.');
        }

        $timings = collect($response->json()['data']['timings'])->only(self::ALLOWED_PRAYERS);
        $this->timings = $timings->map(fn ($time) => date('h:i A', strtotime($time)));
    }

    /**
     * Get the prayer timings for the given location.
     *
     * @return Collection
     */
    public function timings(): Collection
    {
        return $this->timings;
    }
}
