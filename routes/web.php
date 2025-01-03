<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\OpeningHours;
use App\Models\RestaurantConfig;
use App\Http\Middleware\Employee;
use App\Models\User;

//home page
Route::view('/', 'welcome');
Route::redirect('/home', '/');

//about page
Route::view('/about', 'about');
Route::redirect('/information', '/about');

//logout page is not supported -> redirect to home page
Route::get('/logout', function () { return redirect('/'); });

//login and register pages
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

//authenticated pages
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/admin', function () { return auth()->user()->employee ? view('admin') : redirect('/'); })->name('admin');
    Route::redirect('/employees', '/admin');

    //employee panel
    Route::middleware(Employee::class)->group(function () {
        Route::post('/api/admin/search_users', function (Request $request) {
            $request->validate([
                'search' => 'required|string'
            ]);

            $users = User::where('name', 'like', '%'.$request->search.'%')->get();
            $users = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });
            return response()->json(['success' => true, 'users' => $users]);
        });

        Route::post('/api/admin/user_data', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:users,id'
            ]);

            $user = User::find($request->id);
            $reservations = Reservation::where('user_id', $user->id)->with('table')->get();
            foreach ($reservations as $reservation)
            {
                $note = DB::table('reservation_notes')->where('reservation_id', $reservation->id)->first();
                $reservation->note = $note ? $note->note : null;
            }
            $note = DB::table('user_notes')->where('user_id', $user->id)->first();
            $user->note = $note ? $note->note : null;
            $user->employee = $user->employee ? true : false;
            return response()->json(['success' => true, 'user' => $user, 'reservations' => $reservations]);
        });

        Route::post('/api/admin/reservations', function (Request $request) {
            $request->validate([
                'date' => 'required|date'
            ]);

            $reservations = Reservation::where('start_date', $request->date)->orWhere('end_date', $request->date)->with('table', 'user')->get();            
            foreach ($reservations as $reservation)
            {
                $note = DB::table('reservation_notes')->where('reservation_id', $reservation->id)->first();
                $reservation->note = $note ? $note->note : null;
            }
            $tables = Table::all();
            return response()->json(['success' => true, 'reservations' => $reservations, 'tables' => $tables]);
        });

        Route::post('/api/admin/reservation_note', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:reservations,id',
                'note' => 'nullable|string|max:256'
            ]);

            //if reservation has note, update it (if null, delete it) otherwise create it
            if ($request->note === null) { DB::table('reservation_notes')->where('reservation_id', $request->id)->delete(); }
            else { DB::table('reservation_notes')->updateOrInsert(['reservation_id' => $request->id], ['note' => $request->note]); }
            
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/user_note', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:users,id',
                'note' => 'nullable|string|max:256'
            ]);

            //if user has note, update it (if null, delete it) otherwise create it
            if ($request->note === null) { DB::table('user_notes')->where('user_id', $request->id)->delete(); }
            else { DB::table('user_notes')->updateOrInsert(['user_id' => $request->id], ['note' => $request->note]); }
            
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/save_opening_hours', function (Request $request) {
            $request->validate([
                'day' => 'required|string|max:10',
                'open' => 'required_if:closed,false|string|max:5',
                'close' => 'required_if:closed,false|string|max:5',
                'closed' => 'required|boolean'
            ]);
    
            $opening_hours = OpeningHours::updateOrCreate(['day' => $request->day],
                [
                    'closed' => $request->closed,
                    'open' => $request->open,
                    'close' => $request->close,
                    'close_on_next_day' => !$request->closed && DateTime::createFromFormat('H:i', $request->close) < DateTime::createFromFormat('H:i', $request->open)
                ]
            );
    
            return response()->json(['success' => true]);
        });
        Route::post('/api/admin/save_special_hours', function (Request $request) {
            $request->validate([
                'date' => 'required|date',
                'open' => 'required_if:closed,false|string|max:5',
                'close' => 'required_if:closed,false|string|max:5',
                'closed' => 'required|boolean'
            ]);

            //for special dates only include opening/closing times if not closed
            $opening_hours = OpeningHours::updateOrCreate(['day' => $request->date],
                array_merge(
                    ['closed' => $request->closed],
                    !$request->closed ?
                    [
                        'open' => $request->open,
                        'close' => $request->close,
                        'close_on_next_day' => DateTime::createFromFormat('H:i', $request->close) < DateTime::createFromFormat('H:i', $request->open)
                    ]
                    : []
                )
            );
            
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/delete_special_hours', function (Request $request) {
            $request->validate([
                'date' => 'required|date'
            ]);

            $opening_hours = OpeningHours::where('day', $request->date)->delete();
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/create_duration', function (Request $request) {
            $request->validate([
                'duration' => 'required|decimal:0,1|min:0.5'
            ]);

            //only allow whole and half hours (e.g. 1.0, 1.5, 2.0, etc)
            $duration = floatval($request->duration);
            $decimal = $duration - floor($duration);
            if ($decimal != 0 && $decimal != 0.5)
            {
                return response()->json(['success' => false, 'message' => 'Duration decimal part must be either 0 or 0.5.'], 422);
            }

            $durations = json_decode(RestaurantConfig::where('name', 'durations')->first()->value);
            if (in_array($duration, $durations))
            {
                return response()->json(['success' => false, 'message' => 'Duration already exists.'], 422);
            }
            $durations[] = $duration;
            sort($durations);
            RestaurantConfig::where('name', 'durations')->update(['value' => $durations]);
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/delete_duration', function (Request $request) {
            $request->validate([
                'duration' => 'required|decimal:0,1|min:0.5'
            ]);

            $durations = json_decode(RestaurantConfig::where('name', 'durations')->first()->value);
            $durations = array_values(array_diff($durations, [$request->duration]));
            RestaurantConfig::where('name', 'durations')->update(['value' => $durations]);
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/add_employee', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:users,id'
            ]);

            if (!auth()->user()->employee->admin) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

            if (DB::table('employees')->where('user_id', $request->id)->exists()) { return response()->json(['success' => false, 'message' => 'Employee already exists.'], 422); }

            DB::table('employees')->insert(['user_id' => $request->id]);
            $employee = DB::table('users')->join('employees', 'users.id', '=', 'employees.user_id')->select('employees.admin', 'users.email', 'users.name', 'employees.id')->where('users.id', $request->id)->first();

            return response()->json(['success' => true, 'employee' => $employee]);
        });

        Route::post('/api/admin/remove_employee', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:employees,id'
            ]);

            if (!auth()->user()->employee->admin) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

            DB::table('employees')->where('id', $request->id)->delete();
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/set_max_future_reservations', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:users,id',
                'max_future_reservations' => 'nullable|integer|min:0'
            ]);

            User::where('id', $request->id)->update(['max_future_reservations' => $request->max_future_reservations]);
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/save_config', function (Request $request) {
            $request->validate([
                'key' => 'required|string|max:32',
                'value' => 'required|string|max:128'
            ]);

            RestaurantConfig::updateOrCreate(['name' => $request->key], ['value' => $request->value]);
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/save_table', function (Request $request) {
            $request->validate([
                'name' => 'nullable|string|max:64',
                'seats' => 'required|integer|min:0'
            ]);

            $table = Table::create(['name' => $request->name, 'seats' => $request->seats]);
            return response()->json(['success' => true, 'table' => $table]);
        });

        Route::post('/api/admin/delete_table', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:tables,id'
            ]);

            //deleting tables what  have been used for reservations is prohibited as currently there is no way to alert the user as their reservation might be deleted
            if (DB::table('reservations')->where('table_id', $request->id)->exists()) { return response()->json(['success' => false, 'message' => 'Cannot delete table that has reservations.'], 422); }

            Table::where('id', $request->id)->delete();
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/edit_table', function (Request $request) {
            $request->validate([
                'id' => 'required|exists:tables,id',
                'name' => 'nullable|string|max:64',
                'seats' => 'required|integer|min:0'
            ]);

            Table::where('id', $request->id)->update(['name' => $request->name, 'seats' => $request->seats]);
            return response()->json(['success' => true]);
        });

        Route::post('/api/admin/config', function (Request $request) {            
            //get restaurant configuration
            $config = RestaurantConfig::all()->pluck('value', 'name');
            $timezone = $config['timezone'];
            $durations = json_decode($config['durations']);
            $max_future_reservations = intval($config['max_future_reservations']);
            $min_hours_before_reservation = intval($config['min_hours_before_reservation']);
            $max_days_in_advance = intval($config['max_days_in_advance']);
            $phone = $config['phone'];
            $email = $config['email'];
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

            //employees
            $employees = auth()->user()->employee->admin ?
                DB::table('users')->join('employees', 'users.id', '=', 'employees.user_id')->select('employees.admin', 'users.email', 'users.name', 'employees.id')->get()
                : [];

            $tables = Table::all();

            return response()->json([
                'success' => true,
                'tables' => $tables,
                'timezone' => $timezone,
                'opening_hours' => $regular_hours,
                'custom_opening_hours' => $custom_hours,
                'closing_dates' => $closing_dates,
                'durations' => $durations,
                'employees' => $employees,
                'max_future_reservations' => $max_future_reservations,
                'min_hours_before_reservation' => $min_hours_before_reservation,
                'max_days_in_advance' => $max_days_in_advance,
                'phone' => $phone,
                'email' => $email
            ]);
        });
    });
});

//data for pages
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
});

//reservation actions
Route::post('/api/create_reservation', function (Request $request) {
    //must be authenticated
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
    $existing_reservation = DB::table('reservations')
        ->where('table_id', $request->table_id)
        ->where(function($query) use ($new_start, $new_end)
            {
            $query->where(function($q) use ($new_start, $new_end) {
                    //E > NS && E <= NE
                    $q->where(DB::raw('STR_TO_DATE(end_date, "%Y-%m-%d")'), '>', $new_start->format('Y-m-d'))
                    ->where(DB::raw('STR_TO_DATE(end_date, "%Y-%m-%d")'), '<=', $new_end->format('Y-m-d'));
                })
                ->orWhere(function($q) use ($new_start, $new_end) {
                    //S >= NS && S < NE
                    $q->where(DB::raw('STR_TO_DATE(start_date, "%Y-%m-%d")'), '>=', $new_start->format('Y-m-d'))
                    ->where(DB::raw('STR_TO_DATE(start_date, "%Y-%m-%d")'), '<', $new_end->format('Y-m-d'));
                })
                ->orWhere(function($q) use ($new_start, $new_end) {
                    //S <= NS && E >= NE
                    $q->where(DB::raw('STR_TO_DATE(start_date, "%Y-%m-%d")'), '<=', $new_start->format('Y-m-d'))
                    ->where(DB::raw('STR_TO_DATE(end_date, "%Y-%m-%d")'), '>=', $new_end->format('Y-m-d'));
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
            return response()->json(['success' => false, 'message' => 'Table is not available at requested time.'], 422);
        }
        //S >= NS && S < NE
        else if ($start >= $new_start && $start < $new_end)
        {
            return response()->json(['success' => false, 'message' => 'Table is not available at requested time.'], 422);
        }
        //S <= NS && E >= NE
        else if ($start <= $new_start && $end >= $new_end)
        {
            return response()->json(['success' => false, 'message' => 'Table is not available at requested time.'], 422);
        }
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
});

Route::post('/api/delete_reservation', function (Request $request) {
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
});
