<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Middleware\Employee;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AdminController;

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
        Route::post('/api/admin/search_users', [AdminController::class, 'search_users']);
        Route::post('/api/admin/user_data', [AdminController::class, 'user_data']);
        Route::post('/api/admin/reservations', [AdminController::class, 'reservations']);
        Route::post('/api/admin/reservation_note', [AdminController::class, 'reservation_note']);
        Route::post('/api/admin/user_note', [AdminController::class, 'user_note']);
        Route::post('/api/admin/save_opening_hours', [AdminController::class, 'save_opening_hours']);
        Route::post('/api/admin/save_special_hours', [AdminController::class, 'save_special_hours']);
        Route::post('/api/admin/delete_special_hours', [AdminController::class, 'delete_special_hours']);
        Route::post('/api/admin/create_duration', [AdminController::class, 'create_duration']);
        Route::post('/api/admin/delete_duration', [AdminController::class, 'delete_duration']);
        Route::post('/api/admin/add_employee', [AdminController::class, 'add_employee']);
        Route::post('/api/admin/remove_employee', [AdminController::class, 'remove_employee']);
        Route::post('/api/admin/set_max_future_reservations', [AdminController::class, 'set_max_future_reservations']);
        Route::post('/api/admin/save_config', [AdminController::class, 'save_config']);
        Route::post('/api/admin/save_table', [AdminController::class, 'save_table']);
        Route::post('/api/admin/delete_table', [AdminController::class, 'delete_table']);
        Route::post('/api/admin/edit_table', [AdminController::class, 'edit_table']);
        Route::post('/api/admin/config', [AdminController::class, 'config']);
    });
});

//data for pages
Route::post('/api/info_data', [DataController::class, 'info']);
Route::post('/api/restaurant_data', [DataController::class, 'restaurant']);

//reservation actions
Route::post('/api/create_reservation', [ReservationController::class, 'create']);
Route::post('/api/delete_reservation', [ReservationController::class, 'delete']);