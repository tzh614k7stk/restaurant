<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\RestaurantConfig;
use App\Models\OpeningHours;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function info(Request $request)
    {
        //get opening hours
        $opening_hours = OpeningHours::all();

        //process opening hours
        $regular_hours = [];
        $custom_hours = [];
        $closing_dates = [];
        foreach ($opening_hours as $entry)
        {
            //specific dates
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry->day))
            {
                if ($entry->closed) { $closing_dates[] = $entry->day; }
                else
                {
                    $custom_hours[$entry->day] = [
                        'open' => $entry->open,
                        'close' => $entry->close,
                        'close_on_next_day' => $entry->close_on_next_day
                    ];
                }
            }
            else
            {
                //regular days (starting from Sunday because browser date input starts from Sunday)
                $day_number = array_search($entry->day, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
                if ($day_number !== false)
                {
                    if ($entry->closed) { $closing_dates[] = $entry->day; }
                    $regular_hours[$day_number] = [
                        'open' => $entry->open,
                        'close' => $entry->close,
                        'close_on_next_day' => $entry->close_on_next_day
                    ];
                }
            }
        }

        //get restaurant configuration
        $config = RestaurantConfig::all()->pluck('value', 'name');

        //parse data from config
        $phone = $config['phone'];
        $email = $config['email'];

        return response()->json([
            'success' => true,
            'opening_hours' => $regular_hours,
            'custom_opening_hours' => $custom_hours,
            'closing_dates' => $closing_dates,
            'phone' => $phone,
            'email' => $email
        ]);
    }
    
    public function restaurant(Request $request)
    {
        //get all the tables
        $tables = Table::all(['id', 'name', 'seats'])->map(function ($table) {
            return [
                'id' => $table->id,
                'seats' => $table->seats,
                'name' => $table->name
            ];
        });

        //get all the reservations
        $reservations = Reservation::all();

        //get user reservations if authenticated
        $user = auth()->user();
        $user_reservations = $user ? $reservations->where('user_id', $user->id)->pluck('id')->values() : [];

        //get opening hours
        $opening_hours = OpeningHours::all();

        //process opening hours
        $regular_hours = [];
        $custom_hours = [];
        $closing_dates = [];
        foreach ($opening_hours as $entry)
        {
            //specific dates
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry->day))
            {
                if ($entry->closed) { $closing_dates[] = $entry->day; }
                else
                {
                    $custom_hours[$entry->day] = [
                        'open' => $entry->open,
                        'close' => $entry->close,
                        'close_on_next_day' => $entry->close_on_next_day
                    ];
                }
            }
            else
            {
                //regular days (starting from Sunday because browser date input starts from Sunday)
                $day_number = array_search($entry->day, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
                if ($day_number !== false)
                {
                    if ($entry->closed) { $closing_dates[] = $entry->day; }
                    $regular_hours[$day_number] = [
                        'open' => $entry->open,
                        'close' => $entry->close,
                        'close_on_next_day' => $entry->close_on_next_day
                    ];
                }
            }
        }

        //get restaurant configuration
        $config = RestaurantConfig::all()->pluck('value', 'name');

        //parse data from config
        $durations = json_decode($config['durations']);
        $max_days_in_advance = intval($config['max_days_in_advance']);
        $max_future_reservations = intval($config['max_future_reservations']);
        $min_hours_before_reservation = intval($config['min_hours_before_reservation']);
        $timezone = $config['timezone'];

        return response()->json([
            'success' => true,
            'tables' => $tables,
            'reservations' => $reservations,
            'user_reservations' => $user_reservations,
            'opening_hours' => $regular_hours,
            'custom_opening_hours' => $custom_hours,
            'closing_dates' => $closing_dates,
            'durations' => $durations,
            'max_days_in_advance' => $max_days_in_advance,
            'max_future_reservations' => $max_future_reservations,
            'min_hours_before_reservation' => $min_hours_before_reservation,
            'timezone' => $timezone
        ]);
    }
}
