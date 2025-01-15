<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Table;
use App\Models\Employee;
use App\Models\Reservation;
use App\Models\OpeningHours;
use App\Models\RestaurantConfig;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        //create a bunch of other users
        User::factory()->count(10)->create();

        //add test user as an employee + admin
        Employee::create([
            'user_id' => $user->id,
            'admin' => true,
        ]);

        //create tables
        $tables = [
            ['name' => 'Garden Table', 'seats' => 2],
            ['name' => 'Garden Table', 'seats' => 2],
            ['name' => 'Bar Table', 'seats' => 2],
            ['seats' => 2],
            ['seats' => 2],
            ['name' => 'Lake View Table', 'seats' => 4],
            ['name' => 'Lake View Table', 'seats' => 4],
            ['seats' => 4],
            ['seats' => 4],
            ['seats' => 4],
            ['seats' => 4],
            ['name' => 'Private Room', 'seats' => 12],
        ];
        foreach ($tables as $table) { Table::create($table); }

        //create opening hours
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            OpeningHours::create([
                'day' => $day,
                'open' => '10:00',
                'close' => ($day === 'Friday' || $day === 'Saturday') ? '03:00' : '22:00',
                'close_on_next_day' => ($day === 'Friday' || $day === 'Saturday'),
                'closed' => $day === 'Sunday',
            ]);
        }
        OpeningHours::create([
            'day' => '2025-01-01',
            'closed' => true,
        ]);
        OpeningHours::create([
            'day' => '2025-01-02',
            'open' => '11:00',
            'close' => '14:30',
            'closed' => false,
        ]);

        //create reservations for the test user
        $reservations = [
            /*[
                'start_date' => '2024-12-30',
                'end_date' => '2024-12-30',
                'start_time' => '18:00',
                'end_time' => '20:00',
                'duration' => 2.0,
                'seats' => 4,
                'table_id' => 6, //Lake View Table
                'user_id' => $user->id,
            ],
            [
                'start_date' => '2024-12-27',
                'end_date' => '2024-12-27',
                'start_time' => '19:30',
                'end_time' => '21:00',
                'duration' => 1.5,
                'seats' => 2,
                'table_id' => 1, //Garden Table
                'user_id' => $user->id,
            ],
            [
                'start_date' => '2025-01-10',
                'end_date' => '2025-01-11',
                'start_time' => '22:00',
                'end_time' => '01:00',
                'duration' => 3.0,
                'seats' => 12,
                'table_id' => 12, //Private Room
                'user_id' => $user->id,
            ],
            [
                'start_date' => '2025-01-14',
                'end_date' => '2025-01-14',
                'start_time' => '18:00',
                'end_time' => '20:00',
                'duration' => 2.0,
                'seats' => 4,
                'table_id' => 6, //Lake View Table
                'user_id' => $user->id,
            ],
            [
                'start_date' => '2025-01-16',
                'end_date' => '2025-01-16',
                'start_time' => '18:00',
                'end_time' => '20:00',
                'duration' => 2.0,
                'seats' => 4,
                'table_id' => 6, //Lake View Table
                'user_id' => $user->id,
            ],*/
        ];
        foreach ($reservations as $reservation) { Reservation::create($reservation); }

        //create restaurant configuration
        $configs = [
            ['name' => 'max_days_in_advance', 'value' => '60'],
            ['name' => 'durations', 'value' => '[0.5, 1, 1.5, 2, 2.5, 3]'],
            ['name' => 'phone', 'value' => '+420 123 456 789'],
            ['name' => 'email', 'value' => 'info@example.com'],
            ['name' => 'max_future_reservations', 'value' => '5'],
            ['name' => 'timezone', 'value' => 'Europe/Prague'],
            ['name' => 'min_hours_before_reservation', 'value' => '2'],
        ];
        foreach ($configs as $config) { RestaurantConfig::create($config); }
    }
}
