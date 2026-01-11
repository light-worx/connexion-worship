<?php

namespace Modules\Worship\app\Classes;

use Carbon\Carbon;
use Modules\Worship\Models\WeekdayService;

class LiturgicalDateResolver
{
    public static function forYear(int $year): array
    {
        $dates = [];

        $easter = Carbon::createFromTimestamp(easter_date($year));

        foreach (WeekdayService::where('enabled', true)->get() as $day) {
            if ($day->type === 'fixed') {
                $date = Carbon::create($year, $day->month, $day->day);
            } else {
                $date = $easter->copy()->addDays($day->offset);
            }

            $dates[$date->toDateString()] = [
                'name' => $day->name,
                'date' => $date,
            ];
        }

        return $dates;
    }
}
