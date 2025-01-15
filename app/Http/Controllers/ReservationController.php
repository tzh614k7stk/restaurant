<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Models\RestaurantConfig;
use Illuminate\Http\Request;
use DateTime;
use DateTimeZone;
use DateInterval;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        ///must be authenticated
        $user = auth()->user();
        if (!$user) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

        //get restaurant configuration
        $config = RestaurantConfig::all()->pluck('value', 'name');
        $durations = json_decode($config['durations']);
        $max_days_in_advance = intval($config['max_days_in_advance']);
        $max_future_reservations = intval($config['max_future_reservations']);
        $min_hours_before_reservation = intval($config['min_hours_before_reservation']);
        $timezone = $config['timezone'];

        $today = new DateTime('today', new DateTimeZone($timezone));
        $now = new DateTime('now', new DateTimeZone($timezone));
        $max_date = (clone $today)->modify('+'.$max_days_in_advance.' days');

        //validate request data
        $request->validate([
            'date' => 'required|date|after_or_equal:'.$now->format('Y-m-d').'|before_or_equal:'.$max_date->format('Y-m-d'),
            'time' => 'required|date_format:H:i',
            'duration' => 'required|decimal:0,1|in:'.implode(',', $durations),
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'sometimes|required|exists:users,id'
        ]);

        //boundaries of the new reservation
        $new_start = new DateTime($request->date.' '.$request->time, new DateTimeZone($timezone));
        $new_end = (clone $new_start)->add(new DateInterval('PT'.strval($request->duration * 60).'M'));

        //reservation validated to be today or in the future, now check if it is actually in the future in case of today based on time
        if ($now > $new_start) { return response()->json(['success' => false, 'message' => 'Reservation time is in the past.'], 422); }

        //check min hours before reservation
        $min_allowed_start = (clone $now)->add(new DateInterval('PT'.$min_hours_before_reservation.'H'));
        if ($new_start < $min_allowed_start) { return response()->json(['success' => false, 'message' => 'Reservation must be at least '.$min_hours_before_reservation.' '.($min_hours_before_reservation == 1 ? 'hour' : 'hours').' in the future.'], 422); }

        //check if table is available at requested time
        if (!$this->table_available($request->table_id, $new_start, $new_end, $timezone)) {
            return response()->json(['success' => false, 'message' => 'Table is not available at requested time.'], 422);
        }

        //if user_id is provided, check if user exists and current user is employee
        if ($request->user_id)
        {
            $for_user = User::find($request->user_id);
            if (!$for_user) { return response()->json(['success' => false, 'message' => 'User not found.'], 404); }
            if (!$user->employee && $for_user->id !== $user->id) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }
        }

        //check if user has too many future reservations (unless employee)
        if (!$user->employee)
        {
            $future_reservations = Reservation::where('user_id', $user->id)->where('start_date', '>=', $now->format('Y-m-d'))->get();
            $future_reservations = $future_reservations->filter(function($reservation) use ($timezone, $now) { return new DateTime($reservation->start_date . ' ' . $reservation->start_time, new DateTimeZone($timezone)) > $now; })->count();
            $max_future_reservations = $user->max_future_reservations ?? $max_future_reservations;
            if ($future_reservations >= $max_future_reservations) { return response()->json(['success' => false, 'message' => 'Maximum amount of reservations reached.'], 422); }
        }

        //create reservation
        $reservation = Reservation::create([
            'table_id' => $request->table_id,
            'user_id' => $request->user_id ?? $user->id,
            'start_date' => $new_start->format('Y-m-d'),
            'end_date' => $new_end->format('Y-m-d'),
            'start_time' => $new_start->format('H:i'),
            'end_time' => $new_end->format('H:i'),
            'duration' => $request->duration,
            'seats' => Table::find($request->table_id)->seats
        ]);
        return response()->json(['success' => true, 'reservation' => $reservation]);
    }

    public function delete(Request $request)
    {
        //must be authenticated
        $user = auth()->user();
        if (!$user) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

        //validate request data
        $request->validate([
            'id' => 'required|exists:reservations,id'
        ]);

        //get reservation
        $reservation = Reservation::find($request->id);
        if (!$reservation) { return response()->json(['success' => false, 'message' => 'Reservation not found.'], 404); }

        //check if reservation belongs to user or is employee
        if (!$user->employee && $reservation->user_id !== $user->id) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

        //delete reservation
        $reservation->delete();
        return response()->json(['success' => true]);
    }

    private function table_available($table_id, $new_start, $new_end, $timezone)
    {
        //reservations have start_date, end_date (yyyy-mm-dd) and start_time, end_time (hh:mm)
        ////////////////////////////////////////////
        //here you can see which cases handle which overlaps
        //NEW:        |__________|
        //OLD:   |___1__|     |___2__|
        //OLD:          |_1_|
        //OLD   |___________3_________|
        ////////////////////////////////////////////
        //to check date overlaps here are OR conditions (S = existing start, E = existing end, NS = new start, NE = new end):
        //1. E > NS && E <= NE
        //2. S >= NS && S < NE
        //3. S <= NS && E >= NE
        $existing_reservation = Reservation::where('table_id', $table_id)
            ->where(function($query) use ($new_start, $new_end)
                {
                $query->where(function($q) use ($new_start, $new_end) {
                        //E > NS && E <= NE
                        $q->whereRaw('STR_TO_DATE(end_date, "%Y-%m-%d") > ?', [$new_start->format('Y-m-d')])
                        ->whereRaw('STR_TO_DATE(end_date, "%Y-%m-%d") <= ?', [$new_end->format('Y-m-d')]);
                    })
                    ->orWhere(function($q) use ($new_start, $new_end) {
                        //S >= NS && S < NE
                        $q->whereRaw('STR_TO_DATE(start_date, "%Y-%m-%d") >= ?', [$new_start->format('Y-m-d')])
                        ->whereRaw('STR_TO_DATE(start_date, "%Y-%m-%d") < ?', [$new_end->format('Y-m-d')]);
                    })
                    ->orWhere(function($q) use ($new_start, $new_end) {
                        //S <= NS && E >= NE
                        $q->whereRaw('STR_TO_DATE(start_date, "%Y-%m-%d") <= ?', [$new_start->format('Y-m-d')])
                        ->whereRaw('STR_TO_DATE(end_date, "%Y-%m-%d") >= ?', [$new_end->format('Y-m-d')]);
                    });
                }
            )->get();

        //now we have all reservations that might interfere with new reservation in terms of date and need to check if they overlap in time
        foreach ($existing_reservation as $reservation)
        {
            $start = new DateTime($reservation->start_date.' '.$reservation->start_time, new DateTimeZone($timezone));
            $end = new DateTime($reservation->end_date.' '.$reservation->end_time, new DateTimeZone($timezone));
            //E > NS && E <= NE
            if ($end > $new_start && $end <= $new_end)
            {
                return false;
            }
            //S >= NS && S < NE
            else if ($start >= $new_start && $start < $new_end)
            {
                return false;
            }
            //S <= NS && E >= NE
            else if ($start <= $new_start && $end >= $new_end)
            {
                return false;
            }
        }

        return true;
    }
}
