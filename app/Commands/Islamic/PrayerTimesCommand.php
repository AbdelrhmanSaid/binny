<?php

namespace App\Commands\Islamic;

use App\Commands\Command;
use App\Helpers\PrayerTimings;

class PrayerTimesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'prayer:times
                            {city=cairo : The city name}
                            {country=egypt : The country name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get prayer times for a location';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $city = $this->argument('city');
        $country = $this->argument('country');
        $timings = (new PrayerTimings($city, $country))->timings();

        $this->table(
            ['Prayer', 'Time'],
            collect($timings)->map(fn ($time, $prayer) => [$prayer, $time])
        );

        return Command::SUCCESS;
    }
}
