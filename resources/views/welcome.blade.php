<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-auto">

<head>
    <title>{{ config('app.name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <noscript>
        Please enable javascript in order to continue.
        <style>.main-container { display: none; }</style>
    </noscript>

    @vite('resources/css/tailwind.css')
    @vite('resources/css/app.css')

    @vite('resources/js/alpine.js')
    @vite('resources/js/axios.js')

    @livewireStyles
    @livewireScripts
</head>

<body class="bg-gradient-to-b from-zinc-50 to-zinc-300 text-zinc-900">
    <div class="main-container">
        @include('navbar')

        <div class="min-h-screen px-3 leading-relaxed">
            <div class="container py-16 mx-auto">

                <!-- reservation system -->
                <div x-init="get_restaurant_data()" x-data="reservation_system()" class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-y-8">
                    <div x-cloak x-show="first_load" class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-6">
                            <h2 class="text-2xl font-bold tracking-wide">Make a Reservation</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>
                        
                        <!-- date selection -->
                        <div class="mb-6">
                            <label class="block text-zinc-700 text-sm font-bold mb-1">Select Date</label>
                            <input type="date" :min="min_day" :max="max_day" x-model="selected_date" @change="if (!available_tables.find(t => t.id === selected_table)) { selected_table = null; }" class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                        </div>
                        <!-- time selection -->
                        <div class="mb-6">
                            <label class="block text-zinc-700 text-sm font-bold mb-1">Select Time</label>
                            <select x-model="selected_time" @change="if (!available_tables.find(t => t.id === selected_table)) { selected_table = null; }" class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                <option value="" disabled>choose time</option>
                                <template x-for="time in available_times" :key="time">
                                    <option x-text="time" :value="time"></option>
                                </template>
                            </select>
                        </div>
                        <!-- duration selection -->
                        <div class="mb-6">
                            <label class="block text-zinc-700 text-sm font-bold mb-1">Select Duration</label>
                            <select x-model="duration" @change="if (!available_tables.find(t => t.id === selected_table)) { selected_table = null; }" class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                <option value="" disabled>choose duration</option>
                                <template x-for="duration in available_durations" :key="duration">
                                    <option x-text="parse_duration(duration)" :value="duration"></option>
                                </template>
                            </select>
                        </div>
                        <!-- table selection -->
                        <div x-cloak x-show="is_form_valid(false)" class="mb-6">
                            <div x-cloak x-show="available_tables.length > 0">
                                <label class="block text-zinc-700 text-sm font-bold mb-1">Select Table</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template x-for="table in available_tables" :key="table.id">
                                        <div class="p-4 border rounded" :class="{'bg-green-100': selected_table === table.id, 'hover:bg-zinc-100': selected_table !== table.id}" @click="select_table(table.id)">
                                            <p class="font-bold" x-text="table.name ?? 'Table ' + table.id"></p>
                                            <p class="text-sm text-zinc-600 flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                                <span x-text="table.seats + ' seats'"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <!-- errors -->
                        <div class="mb-6">
                            <div x-cloak x-show="is_form_valid(false) && available_tables.length === 0">
                                <p class="text-rose-700 text-sm font-bold mb-1">No tables available on this day for the selected time and duration.</p>
                            </div>
                            <div x-cloak x-show="closing_dates.includes(selected_date) || closing_dates.includes(new Date(selected_date).toLocaleDateString('en-US', {weekday: 'long'}))">
                                <p class="text-rose-700 text-sm font-bold mb-1">We are closed on this date.</p>
                            </div>
                        </div>
                        <!-- submit button -->
                        <button @click="submit_reservation" :class="is_form_valid(true) ? 'bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950' : 'bg-zinc-500 opacity-50'" class="text-white font-bold rounded py-2 px-3 focus:outline-none">Make Reservation</button>
                    </div>

                    <!-- user reservations -->
                    @auth
                    <div x-cloak x-show="user_reservations.length > 0" class="bg-white overflow-hidden shadow-md rounded-lg">
                        <div class="flex flex-col gap-y-6 px-6 py-8">
                            <div class="flex items-center gap-2">
                                <h2 class="text-2xl font-bold tracking-wide">Your Reservations</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                </svg>
                            </div>
                            <!-- future reservations -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-screen overflow-y-auto">
                                <div x-cloak x-show="user_reservations.filter(id => { const reservation = table_reservations.find(r => r.id === id); return new Date(reservation.date) >= new Date().setHours(0,0,0,0); }).length === 0" class="col-span-full">
                                    <p class="text-zinc-600">You have no upcoming reservations.</p>
                                </div>
                                <template x-for="reservation_id in user_reservations.filter(id => { const reservation = table_reservations.find(r => r.id === id); return new Date(reservation.date) >= new Date().setHours(0,0,0,0); })" :key="reservation_id">
                                    <div x-data="{ reservation: table_reservations.find(r => r.id === reservation_id) }" class="p-4 border rounded">
                                        <p class="font-bold" x-text="tables.find(t => t.id === reservation.table_id).name ?? 'Table ' + reservation.table_id"></p>
                                        <p class="text-sm text-zinc-600">
                                            <span class="flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                                <span x-text="'Seats: ' + reservation.seats"></span>
                                            </span>
                                            <span class="flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                </svg>
                                                <span x-text="'Date: ' + new Date(reservation.date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})"></span>
                                            </span>
                                            <span class="flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                <span x-text="'Time: ' + reservation.time"></span>
                                            </span>
                                            <span class="flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                                </svg>
                                                <span x-text="'Duration: ' + parse_duration(reservation.duration)"></span>
                                            </span>
                                            <div class="mt-2 flex flex-col gap-y-1">
                                                <button @click="add_to_google_calendar(reservation.id)" class="text-sm font-semibold text-zinc-600 hover:text-zinc-700 flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                                    </svg>
                                                    Add to Google Calendar
                                                </button>
                                                <button @click="$store.modal.open('Cancel Reservation', 'Are you sure you want to cancel this reservation?', () => cancel_reservation(reservation.id), 'Yes, cancel it', 'No, keep it', 'bg-rose-600 hover:bg-rose-700')" class="text-sm font-semibold text-rose-600 hover:text-rose-700 flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    Cancel Reservation
                                                </button>
                                            </div>
                                        </p>
                                    </div>
                                </template>
                            </div>
                            <!-- past reservations -->
                            <div x-cloak x-show="user_reservations.filter(id => { const reservation = table_reservations.find(r => r.id === id); return new Date(reservation.date) < new Date().setHours(0,0,0,0); }).length > 0" x-data="{ show_past: false }" class="flex flex-col gap-4">
                                <button @click="show_past = !show_past" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': show_past }">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <span x-text="show_past ? 'Hide Past Reservations' : 'Show Past Reservations'"></span>
                                </button>
                                
                                <div x-show="show_past" x-collapse class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-screen overflow-y-auto">
                                    <template x-for="reservation_id in user_reservations.filter(id => { const reservation = table_reservations.find(r => r.id === id); return new Date(reservation.date) < new Date().setHours(0,0,0,0); })" :key="reservation_id">
                                        <div x-data="{ reservation: table_reservations.find(r => r.id === reservation_id) }" class="p-4 border rounded opacity-75">
                                            <p class="font-bold" x-text="tables.find(t => t.id === reservation.table_id).name ?? 'Table ' + reservation.table_id"></p>
                                            <p class="text-sm text-zinc-600">
                                                <span class="flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                    </svg>
                                                    <span x-text="'Seats: ' + reservation.seats"></span>
                                                </span>
                                                <span class="flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                    </svg>
                                                    <span x-text="'Date: ' + new Date(reservation.date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})"></span>
                                                </span>
                                                <span class="flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    <span x-text="'Time: ' + reservation.time"></span>
                                                </span>
                                                <span class="flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                                    </svg>
                                                    <span x-text="'Duration: ' + parse_duration(reservation.duration)"></span>
                                                </span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- user info -->
                    <div x-cloak x-show="first_load" class="bg-white overflow-hidden shadow-md rounded-lg">
                        <div class="flex flex-col gap-y-6 px-6 py-8">
                            <div class="flex flex-col items-start">
                                <p class="flex items-center text-lg text-zinc-700 border-b border-zinc-400 mb-2 pr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="text-zinc-700 text-sm">
                                    <i class="text-zinc-500 pr-1">email:</i> <a>{{ Auth::user()->email }}</a>
                                </p>
                                <p class="text-zinc-700 text-sm">
                                    <i class="text-zinc-500 pr-1">member since:</i> <a>{{ Auth::user()->created_at->format('F Y') }}</a>
                                </p>
                                <p class="text-zinc-700 text-sm">
                                    <i class="text-zinc-500 pr-1">number of reservations:</i> <a x-text="user_reservations.length"></a>
                                </p>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center text-lg text-zinc-700 hover:text-zinc-800 border-t border-zinc-400 mt-2 pr-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endauth
                    @guest
                    <div class="bg-white overflow-hidden shadow-md rounded-lg">
                        <div class="px-6 py-8">
                            <div class="flex justify-center items-center gap-6">
                                <a href="{{ route('login') }}" class="text-zinc-700 hover:text-zinc-800 text-lg flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Login
                                </a>
                                <span class="text-zinc-700 text-xl">|</span>
                                <a href="{{ route('register') }}" class="text-zinc-700 hover:text-zinc-800 text-lg flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                    </svg>
                                    Register
                                </a>
                            </div>
                        </div>
                    </div>
                    @endguest

                    @include('modal')
                </div>

                <script>
                    function reservation_system() {
                        return {
                            //server configuration
                            first_load: false,
                            max_days_in_advance: null,
                            durations: [],
                            opening_hours: {},
                            custom_opening_hours: {},
                            closing_dates: [],
                            tables: [],
                            table_reservations: [],
                            user_reservations: [],
                            get_restaurant_data() {
                                axios.post('/api/restaurant_data').then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.max_days_in_advance = data.max_days_in_advance;
                                        this.durations = data.durations;
                                        this.opening_hours = data.opening_hours;
                                        this.custom_opening_hours = data.custom_opening_hours;
                                        this.closing_dates = data.closing_dates;
                                        this.tables = data.tables;
                                        this.table_reservations = data.table_reservations;
                                        this.user_reservations = data.user_reservations;
                                    }
                                    else
                                    {
                                        Alpine.store('modal').open(
                                            'Error',
                                            'Failed to load restaurant configuration. Please try refreshing the page. Server error.',
                                            null,
                                            'OK',
                                            'Cancel',
                                            'bg-rose-600 hover:bg-rose-700'
                                        );
                                    }
                                }).catch(error => {
                                    Alpine.store('modal').open(
                                        'Error',
                                        'Failed to load restaurant configuration. Please try refreshing the page. Client error.',
                                        null,
                                        'OK',
                                        'Cancel',
                                        'bg-rose-600 hover:bg-rose-700'
                                    );
                                }).finally(() => {
                                    this.first_load = true; //fix min/max attribute causing date input to flicker
                                });
                            },

                            //user selection
                            selected_date: '', //date input format is based on user's locale, all date variables are yyyy-mm-dd
                            selected_time: '',
                            duration: '',
                            selected_table: null,
                            //date input is based on user's locale, min and max must follow
                            get min_day() {
                                return new Date().toLocaleDateString('en-CA');
                            },
                            get max_day() {
                                const date = new Date(this.min_day);
                                date.setHours(0, 0, 0, 0); //matching php max_day calculation
                                date.setDate(date.getDate() + this.max_days_in_advance);
                                return date.toLocaleDateString('en-CA');
                            },

                            //reservation actions
                            add_to_google_calendar(reservation_id) {
                                const reservation = this.table_reservations.find(r => r.id === reservation_id);

                                //convert times to google calendar format
                                const start_time = new Date(`${reservation.date}T${reservation.time}`);
                                const end_time = new Date(start_time.getTime() + (reservation.duration * 3600 * 1000));
                                const start_iso = start_time.getFullYear() +
                                    String(start_time.getMonth() + 1).padStart(2, '0') +
                                    String(start_time.getDate()).padStart(2, '0') + 
                                    'T' +
                                    String(start_time.getHours()).padStart(2, '0') +
                                    String(start_time.getMinutes()).padStart(2, '0') +
                                    '00';
                                const end_iso = end_time.getFullYear() +
                                    String(end_time.getMonth() + 1).padStart(2, '0') +
                                    String(end_time.getDate()).padStart(2, '0') + 
                                    'T' +
                                    String(end_time.getHours()).padStart(2, '0') +
                                    String(end_time.getMinutes()).padStart(2, '0') +
                                    '00';
                                
                                let google_calendar_url = 'https://calendar.google.com/calendar/r/eventedit';
                                google_calendar_url += '?text=' + encodeURIComponent("Reservation at {{ config('app.name') }}");
                                google_calendar_url += '&dates=' + encodeURIComponent(start_iso + '/' + end_iso);
                                google_calendar_url += '&details=' + encodeURIComponent('Reservation at Table ' + reservation.table_id + '.');
                                window.open(google_calendar_url, '_blank');
                            },
                            cancel_reservation(reservation_id) {
                                axios.post('/api/delete_reservation', { id: reservation_id }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.user_reservations = this.user_reservations.filter(id => id !== reservation_id);
                                        this.table_reservations = this.table_reservations.filter(reservation => reservation.id !== reservation_id);
                                    }
                                    else
                                    {
                                        Alpine.store('modal').open(
                                            'Error',
                                            'Failed to cancel reservation. Please try refreshing the page. Server error.',
                                            null,
                                            'OK',
                                            'Cancel',
                                            'bg-rose-600 hover:bg-rose-700'
                                        );
                                    }
                                }).catch(error => {
                                    Alpine.store('modal').open(
                                        'Error',
                                        'Failed to cancel reservation. Please try refreshing the page. Client error.',
                                        null,
                                        'OK',
                                        'Cancel',
                                        'bg-rose-600 hover:bg-rose-700'
                                    );
                                });
                            },

                            //form getters
                            get available_times() {
                                if (!this.selected_date) { return []; }
                                if (this.closing_dates.includes(this.selected_date) || this.closing_dates.includes(new Date(this.selected_date).toLocaleDateString('en-US', {weekday: 'long'}))) { return []; }
                                
                                //first check custom hours
                                const custom_hours = this.custom_opening_hours[this.selected_date];
                                if (custom_hours)
                                {
                                    const { open, close } = custom_hours;
                                    return this.generate_time_slots(open, close);
                                }
                                
                                //check regular hours
                                const day = new Date(this.selected_date).getDay();
                                const { open, close } = this.opening_hours[day];
                                return this.generate_time_slots(open, close);
                            },
                            get available_durations() {
                                if (this.closing_dates.includes(this.selected_date) || this.closing_dates.includes(new Date(this.selected_date).toLocaleDateString('en-US', {weekday: 'long'}))) { return []; }
                                if (!this.selected_time || !this.selected_date) { return this.durations; }
                                
                                const custom_hours = this.custom_opening_hours[this.selected_date];
                                const day = new Date(this.selected_date).getDay();
                                const { close } = custom_hours || this.opening_hours[day];
                                const { open } = custom_hours || this.opening_hours[day];
                                
                                const [hours, minutes] = this.selected_time.split(':');
                                const selected_hour = parseInt(hours);
                                const selected_minutes = parseInt(minutes);
                                let hours_until_close;
                                
                                const close_hour = parseInt(close);
                                const close_minutes = close.includes(':') ? parseInt(close.split(':')[1]) : 0;
                                
                                if (close_hour < selected_hour) //selected time is before midnight, closing is after
                                {
                                    hours_until_close = (24 - selected_hour) + close_hour;
                                    if (close_minutes === 30) hours_until_close += 0.5;
                                    if (selected_minutes === 30) hours_until_close -= 0.5;
                                }
                                else if (selected_hour < close_hour && close_hour < open) //selected time is after midnight
                                {
                                    hours_until_close = close_hour - selected_hour;
                                    if (close_minutes === 30) hours_until_close += 0.5;
                                    if (selected_minutes === 30) hours_until_close -= 0.5;
                                }
                                else //normal case
                                {
                                    hours_until_close = close_hour - selected_hour;
                                    if (close_minutes === 30) hours_until_close += 0.5;
                                    if (selected_minutes === 30) hours_until_close -= 0.5;
                                }
                                
                                return this.durations.filter(duration => duration <= hours_until_close);
                            },
                            get available_tables() {
                                if (!this.selected_date || !this.selected_time || !this.duration || this.closing_dates.includes(this.selected_date) || this.closing_dates.includes(new Date(this.selected_date).toLocaleDateString('en-US', {weekday: 'long'}))) { return []; }

                                const reserved_tables = this.table_reservations.filter(reservation => {
                                    if (reservation.date !== this.selected_date) { return false; }

                                    //convert times to comparable numbers (hours since midnight)
                                    const reservation_time = reservation.time.split(':').reduce((acc, val, i) => acc + (i === 0 ? parseFloat(val) : parseFloat(val) / 60), 0);
                                    const selected_time = this.selected_time.split(':').reduce((acc, val, i) => acc + (i === 0 ? parseFloat(val) : parseFloat(val) / 60), 0);
                                    
                                    //check if time periods overlap
                                    const reservation_end = reservation_time + parseFloat(reservation.duration);
                                    const selected_end = selected_time + parseFloat(this.duration);
                                    
                                    //handle after midnight cases
                                    const custom_hours = this.custom_opening_hours[this.selected_date];
                                    const day = new Date(this.selected_date).getDay();
                                    const { open, close } = custom_hours || this.opening_hours[day];
                                    
                                    const open_hour = parseInt(open);
                                    const close_hour = parseInt(close);
                                    
                                    if (close_hour < open_hour)
                                    {
                                        //normalize times to handle midnight crossing
                                        const normalize = t => t >= open_hour ? t : t + 24;
                                        const norm_reservation = normalize(reservation_time);
                                        const norm_reservation_end = normalize(reservation_end);
                                        const norm_selected = normalize(selected_time);
                                        const norm_selected_end = normalize(selected_end);                                        
                                        return !(norm_selected >= norm_reservation_end || norm_selected_end <= norm_reservation);
                                    }
                                    
                                    return !(selected_time >= reservation_end || selected_end <= reservation_time);
                                }).map(reservation => reservation.table_id);

                                return this.tables.filter(table => !reserved_tables.includes(table.id));
                            },

                            //form helpers
                            parse_duration(duration) {
                                let result = '';
                                let hours = Math.floor(duration);
                                let minutes = Math.floor((duration - hours) * 60);
                                if (hours > 0) { result += hours + ' hour' + (hours > 1 ? 's' : ''); }
                                if (minutes > 0) { result += ' ' + minutes + ' minutes'; }
                                return result;
                            },
                            generate_time_slots(open, close) {
                                const times = [];
                                const open_hour = parseInt(open);
                                const open_minutes = open.includes(':') ? parseInt(open.split(':')[1]) : 0;
                                const close_hour = parseInt(close);
                                const close_minutes = close.includes(':') ? parseInt(close.split(':')[1]) : 0;
                                
                                if (close_hour < open_hour) //open after midnight
                                {
                                    //times not crossing midnight
                                    for (let hour = open_hour; hour < 24; ++hour)
                                    {
                                        if (hour === open_hour && open_minutes === 30)
                                        {
                                            times.push(`${hour.toString().padStart(2, '0')}:30`);
                                        }
                                        else
                                        {
                                            times.push(`${hour.toString().padStart(2, '0')}:00`);
                                            times.push(`${hour.toString().padStart(2, '0')}:30`);
                                        }
                                    }
                                    //times crossing midnight
                                    for (let hour = 0; hour < close_hour; ++hour)
                                    {
                                        times.push(`${hour.toString().padStart(2, '0')}:00`);
                                        times.push(`${hour.toString().padStart(2, '0')}:30`);
                                    }
                                    if (close_minutes === 30)
                                    {
                                        times.push(`${close_hour.toString().padStart(2, '0')}:00`);
                                    }
                                }
                                else //open before midnight
                                {
                                    for (let hour = open_hour; hour < close_hour; ++hour)
                                    {
                                        if (hour === open_hour && open_minutes === 30)
                                        {
                                            times.push(`${hour.toString().padStart(2, '0')}:30`);
                                        }
                                        else
                                        {
                                            times.push(`${hour.toString().padStart(2, '0')}:00`);
                                            times.push(`${hour.toString().padStart(2, '0')}:30`);
                                        }
                                    }
                                    if (close_minutes === 30) { times.push(`${close_hour.toString().padStart(2, '0')}:00`); }
                                }
                                return times;
                            },
                            is_form_valid(with_table) {
                                return this.selected_date && 
                                       this.selected_date >= this.min_day && 
                                       this.selected_date <= this.max_day && 
                                       !this.closing_dates.includes(this.selected_date) &&
                                       !this.closing_dates.includes(new Date(this.selected_date).toLocaleDateString('en-US', {weekday: 'long'})) &&
                                       this.selected_time && 
                                       this.duration && 
                                       (with_table ? this.selected_table : true);
                            },

                            //form actions
                            select_table(table_id) { this.selected_table = table_id; },
                            submit_reservation() {
                                if (!this.is_form_valid(true)) { return; }

                                axios.post('/api/create_reservation', {
                                    date: this.selected_date,
                                    time: this.selected_time,
                                    duration: this.duration,
                                    table_id: this.selected_table
                                }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        const reservation = data.reservation;
                                        //insert into local variables
                                        this.table_reservations.push(reservation);
                                        this.user_reservations.push(reservation.id);
                                        //sort user reservations by date and time
                                        this.user_reservations.sort((a, b) => {
                                            a = this.table_reservations.find(reservation => reservation.id === a);
                                            b = this.table_reservations.find(reservation => reservation.id === b);
                                            const dateA = new Date(`${a.date} ${a.time}`);
                                            const dateB = new Date(`${b.date} ${b.time}`);
                                            return dateA - dateB;
                                        });
                                    }
                                    else
                                    {
                                        Alpine.store('modal').open(
                                            'Error',
                                            'Failed to create reservation. Please try refreshing the page. Client error.',
                                            null,
                                            'OK',
                                            'Cancel',
                                            'bg-rose-600 hover:bg-rose-700'
                                        );
                                    }
                                }).catch(error => {
                                    Alpine.store('modal').open(
                                        'Error',
                                        (error.response && error.response.status === 401) ? 'You must be logged in to make a reservation.' : 'Failed to create reservation. Please try refreshing the page. Server error.',
                                        (error.response && error.response.status === 401) ? () => window.location.href = '/login' : null,
                                        (error.response && error.response.status === 401) ? 'Login' : 'OK',
                                        'Cancel',
                                        (error.response && error.response.status === 401) ? 'bg-zinc-700 hover:bg-zinc-800' : 'bg-rose-600 hover:bg-rose-700'
                                    );
                                }).finally(() => {
                                    //reset form
                                    this.selected_date = '';
                                    this.selected_time = '';
                                    this.duration = '';
                                    this.selected_table = null;
                                });
                            },
                        }
                    }
                </script>
                <!-- reservation system end -->

            </div>            
        </div>

        <?php if (app()->environment('local')) { echo "Laravel v" . Illuminate\Foundation\Application::VERSION . " | PHP v" . PHP_VERSION; } ?>

    </div>
</body>

</html>