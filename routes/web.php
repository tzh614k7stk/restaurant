<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\OpeningHours;
use App\Models\RestaurantConfig;

Route::view('/', 'welcome');
Route::redirect('/home', '/');

Route::view('/about', 'about');
Route::redirect('/information', '/about');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

Route::post('/api/info_data', function () {
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
                    'open' => substr($entry->open, 0, 5),
                    'close' => substr($entry->close, 0, 5)
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
                    'open' => substr($entry->open, 0, 5),
                    'close' => substr($entry->close, 0, 5)
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
});

Route::post('/api/restaurant_data', function () {    
    //get all the tables
    $tables = Table::all(['id', 'name', 'seats'])->map(function ($table) {
        return [
            'id' => $table->id,
            'seats' => $table->seats,
            'name' => $table->name
        ];
    });

    //get all the reservations
    $reservations = Reservation::all()->map(function ($reservation) {
        return [
            'id' => $reservation->id,
            'date' => date('Y-m-d', strtotime($reservation->date)),
            'time' => date('H:i', strtotime($reservation->time)),
            'duration' => $reservation->duration,
            'seats' => $reservation->seats,
            'table_id' => $reservation->table_id,
            'user_id' => $reservation->user_id
        ];
    });

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
                    'open' => substr($entry->open, 0, 5),
                    'close' => substr($entry->close, 0, 5)
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
                    'open' => substr($entry->open, 0, 5),
                    'close' => substr($entry->close, 0, 5)
                ];
            }
        }
    }

    //get restaurant configuration
    $config = RestaurantConfig::all()->pluck('value', 'name');
    
    //parse data from config
    $durations = json_decode($config['durations']);
    $max_days_in_advance = intval($config['max_days_in_advance']);

    return response()->json([
        'success' => true,
        'tables' => $tables,
        'table_reservations' => $reservations,
        'user_reservations' => $user_reservations,
        'opening_hours' => $regular_hours,
        'custom_opening_hours' => $custom_hours,
        'closing_dates' => $closing_dates,
        'durations' => $durations,
        'max_days_in_advance' => $max_days_in_advance
    ]);
});

Route::post('/api/create_reservation', function (Request $request) {
    //must be authenticated
    $user = auth()->user();
    if (!$user) { return response()->json(['message' => 'Unauthorized'], 401); }

    //get restaurant configuration
    $config = RestaurantConfig::all()->pluck('value', 'name');
    $durations = json_decode($config['durations']);
    $max_days_in_advance = intval($config['max_days_in_advance']);

    //validate request data
    $request->validate([
        'date' => 'required|date|after_or_equal:today|before_or_equal:'.date('Y-m-d', strtotime('+'.$max_days_in_advance.' days')),
        'time' => 'required|date_format:H:i',
        'duration' => 'required|numeric|in:'.implode(',', $durations),
        'table_id' => 'required|exists:tables,id'
    ]);

    //check if table is available at requested time
    $existing_reservation = Reservation::where('table_id', $request->table_id)->where('date', $request->date)->where(function($query) use ($request) {
        //calculate start and end time of reservation
        $start_time = strtotime($request->time);
        $end_time = strtotime("+{$request->duration} hours", $start_time);

        //check if reservation overlaps with existing reservations
        $query->where(function($q) use ($start_time, $end_time, $request) {
            $q->where('time', '<=', date('H:i', $start_time))->whereRaw("ADDTIME(time, SEC_TO_TIME(duration * 3600)) > ?", [date('H:i', $start_time)]);
        })->orWhere(function($q) use ($start_time, $end_time, $request) {
            $q->where('time', '<', date('H:i', $end_time))->where('time', '>', date('H:i', $start_time));
        });
    })->first();
    if ($existing_reservation) { return response()->json(['message' => 'Table is not available at requested time'], 422); }

    //create reservation
    $reservation = Reservation::create([
        'date' => date('Y-m-d', strtotime($request->date)),
        'time' => date('H:i', strtotime($request->time)),
        'duration' => $request->duration,
        'seats' => Table::find($request->table_id)->seats,
        'table_id' => $request->table_id,
        'user_id' => $user->id
    ]);
    return response()->json(['reservation' => $reservation, 'success' => true]);
});

Route::post('/api/delete_reservation', function (Request $request) {
    //must be authenticated
    $user = auth()->user();
    if (!$user) { return response()->json(['message' => 'Unauthorized'], 401); }

    //validate request data
    $request->validate([
        'id' => 'required|exists:reservations,id'
    ]);

    //get reservation
    $reservation = Reservation::find($request->id);
    if (!$reservation) { return response()->json(['message' => 'Reservation not found'], 404); }

    //check if reservation belongs to user or is employee
    if ($reservation->user_id !== $user->id && !$user->is_employee) { return response()->json(['message' => 'Unauthorized'], 401); }

    //delete reservation
    $reservation->delete();
    return response()->json(['success' => true]);
});
