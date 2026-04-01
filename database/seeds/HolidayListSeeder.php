<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HolidayListSeeder extends Seeder
{
    public function run()
    {
        $holidays = [
            // RECURRING NATIONAL
            ['name' => 'Republic Day', 'date' => '2000-01-26', 'city' => null, 'is_national' => true, 'is_recurring' => true, 'type' => 'gazetted'],
            ['name' => 'Independence Day', 'date' => '2000-08-15', 'city' => null, 'is_national' => true, 'is_recurring' => true, 'type' => 'gazetted'],
            ['name' => 'Mahatma Gandhi Jayanti', 'date' => '2000-10-02', 'city' => null, 'is_national' => true, 'is_recurring' => true, 'type' => 'gazetted'],

            // LUDHIANA SPECIFIC 2026
            ['name' => 'Lohri', 'date' => '2026-01-13', 'city' => 'Ludhiana', 'is_national' => false, 'is_recurring' => false, 'type' => 'regional'],
            ['name' => 'Vaisakhi', 'date' => '2026-04-14', 'city' => 'Ludhiana', 'is_national' => false, 'is_recurring' => false, 'type' => 'gazetted'],
            ['name' => 'Guru Nanak Dev Ji Gurpurb', 'date' => '2026-11-24', 'city' => 'Ludhiana', 'is_national' => false, 'is_recurring' => false, 'type' => 'gazetted'],
            ['name' => 'Vishwakarma Day', 'date' => '2026-11-09', 'city' => 'Ludhiana', 'is_national' => false, 'is_recurring' => false, 'type' => 'gazetted'],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holiday_lists')->updateOrInsert(
                ['name' => $holiday['name'], 'city' => $holiday['city'], 'date' => $holiday['date']],
                array_merge($holiday, [
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }
}