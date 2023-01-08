<?php

namespace App\Commands;

use App\Helpers\PrayerTimings;

class PrayerTimesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'prayer:times {city=cairo} {country=egypt} {--all}';

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
        $all = $this->option('all');

        $timings = (new PrayerTimings($city, $country))
            ->{$all ? 'all' : 'required'}();

        $this->table(
            ['Prayer', 'Time'],
            collect($timings)->map(function ($time, $prayer) {
                return [$prayer, $this->convertTime($time)];
            })->toArray()
        );

        return Command::SUCCESS;
    }

    /**
     * Convert 24-hour time to 12-hour time.
     *
     * @param  string  $time
     * @return string
     */
    protected function convertTime(string $time): string
    {
        $time = date('h:i A', strtotime($time));

        return $time;
    }
}
