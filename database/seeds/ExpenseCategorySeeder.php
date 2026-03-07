<?php

use Illuminate\Database\Seeder;
use App\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Distance Travelled', 'details' => 'Personal Vehicle Usage', 'is_travel' => 1],
            ['name' => 'Intercity Taxi Charge', 'details' => null, 'is_travel' => 0],
            ['name' => 'Train/ Bus Tickets', 'details' => null, 'is_travel' => 0],
            ['name' => 'Air Tickets', 'details' => null, 'is_travel' => 0],
            ['name' => 'Local Travel Expense', 'details' => '(Metro/ Bus/ Cab/ Autorickshaw/ Rickshaw)', 'is_travel' => 0],
            ['name' => 'Boarding Expenses', 'details' => '(Hotel/ Guest House etc.)', 'is_travel' => 0],
            ['name' => 'Personal Meal Expenses', 'details' => null, 'is_travel' => 0],
            ['name' => 'Client Meeting Charges', 'details' => null, 'is_travel' => 0],
            ['name' => 'Airport Parking', 'details' => null, 'is_travel' => 0],
            ['name' => 'Other Misc. Expenses', 'details' => null, 'is_travel' => 0],
        ];

        foreach ($data as $item) {
            ExpenseCategory::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}