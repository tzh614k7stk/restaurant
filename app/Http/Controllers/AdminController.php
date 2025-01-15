<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Models\Employee;
use App\Models\RestaurantConfig;
use App\Models\OpeningHours;
use App\Models\ReservationNote;
use App\Models\UserNote;
use Illuminate\Http\Request;
use DateTime;

class AdminController extends Controller
{
    public function search_users(Request $request)
    {
        $request->validate([
            'search' => 'required|string'
        ]);

        $users = User::where('name', 'like', '%'.$request->search.'%')->select('id', 'name')->get();
        return response()->json(['success' => true, 'users' => $users]);
    }

    public function user_data(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->id);
        $reservations = Reservation::where('user_id', $user->id)->with('table')->get();
        foreach ($reservations as $reservation)
        {
            $note = ReservationNote::where('reservation_id', $reservation->id)->first();
            $reservation->note = $note ? $note->note : null;
        }
        $note = UserNote::where('user_id', $user->id)->first();
        $user->note = $note ? $note->note : null;
        $user->employee = $user->employee ? true : false;
        return response()->json(['success' => true, 'user' => $user, 'reservations' => $reservations]);
    }

    public function reservations(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $reservations = Reservation::where('start_date', $request->date)->orWhere('end_date', $request->date)->with('table', 'user')->get();
        foreach ($reservations as $reservation)
        {
            $note = ReservationNote::where('reservation_id', $reservation->id)->first();
            $reservation->note = $note ? $note->note : null;
        }
        $tables = Table::all();
        return response()->json(['success' => true, 'reservations' => $reservations, 'tables' => $tables]);
    }

    public function reservation_note(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:reservations,id',
            'note' => 'nullable|string|max:256'
        ]);

        //if reservation has note, update it (if null, delete it) otherwise create it
        if ($request->note === null) { ReservationNote::where('reservation_id', $request->id)->delete(); }
        else { ReservationNote::updateOrCreate(['reservation_id' => $request->id], ['note' => $request->note]); }

        return response()->json(['success' => true]);
    }

    public function user_note(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:256'
        ]);

        //if user has note, update it (if null, delete it) otherwise create it
        if ($request->note === null) { UserNote::where('user_id', $request->id)->delete(); }
        else { UserNote::updateOrCreate(['user_id' => $request->id], ['note' => $request->note]); }

        return response()->json(['success' => true]);
    }

    public function save_opening_hours(Request $request)
    {
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
    }

    public function save_special_hours(Request $request)
    {
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
    }

    public function delete_special_hours(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $opening_hours = OpeningHours::where('day', $request->date)->delete();
        return response()->json(['success' => true]);
    }

    public function create_duration(Request $request)
    {
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
    }

    public function delete_duration(Request $request)
    {
        $request->validate([
            'duration' => 'required|decimal:0,1|min:0.5'
        ]);

        $durations = json_decode(RestaurantConfig::where('name', 'durations')->first()->value);
        $durations = array_values(array_diff($durations, [$request->duration]));
        RestaurantConfig::where('name', 'durations')->update(['value' => $durations]);
        return response()->json(['success' => true]);
    }

    public function add_employee(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        if (!auth()->user()->employee->admin) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

        if (Employee::where('user_id', $request->id)->exists()) { return response()->json(['success' => false, 'message' => 'Employee already exists.'], 422); }

        $employee = Employee::create(['user_id' => $request->id]);
        $employee = User::with('employee')->select('email', 'name')->find($request->id);

        return response()->json(['success' => true, 'employee' => $employee]);
    }

    public function remove_employee(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:employees,id'
        ]);

        if (!auth()->user()->employee->admin) { return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401); }

        Employee::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function set_max_future_reservations(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'max_future_reservations' => 'nullable|integer|min:0'
        ]);

        User::where('id', $request->id)->update(['max_future_reservations' => $request->max_future_reservations]);
        return response()->json(['success' => true]);
    }

    public function save_config(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:32',
            'value' => 'required|string|max:128'
        ]);

        RestaurantConfig::updateOrCreate(['name' => $request->key], ['value' => $request->value]);
        return response()->json(['success' => true]);
    }

    public function save_table(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:64',
            'seats' => 'required|integer|min:0'
        ]);

        $table = Table::create(['name' => $request->name, 'seats' => $request->seats]);
        return response()->json(['success' => true, 'table' => $table]);
    }

    public function delete_table(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tables,id'
        ]);

        //deleting tables what  have been used for reservations is prohibited as currently there is no way to alert the user as their reservation might be deleted
        if (Table::find($request->id)->reservations()->exists()) { return response()->json(['success' => false, 'message' => 'Cannot delete table that has reservations.'], 422); }

        Table::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function edit_table(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tables,id',
            'name' => 'nullable|string|max:64',
            'seats' => 'required|integer|min:0'
        ]);

        Table::where('id', $request->id)->update(['name' => $request->name, 'seats' => $request->seats]);
        return response()->json(['success' => true]);
    }

    public function config(Request $request)
    {
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
            User::join('employees', 'users.id', '=', 'employees.user_id')->select('employees.admin', 'users.email', 'users.name', 'employees.id')->get()
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
    }
}
