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
                    <template x-cloak x-if="first_load">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">Make a Reservation</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                </svg>
                            </div>
                            
                            <!-- employee user selection -->
                            @auth
                            @if (Auth::user()->employee)
                            <div class="mb-6">
                                <label class="block text-zinc-700 text-sm font-bold mb-1 flex flex-row items-center gap-x-2">Select User
                                    <template x-cloak x-if="selected_user">
                                        <div class="flex flex-row items-center font-normal">
                                            <p class="text-zinc-600">(selected user: <span class="text-rose-400" x-text="selected_user.name"></span>)</p>
                                            <button @click="clear_user()" class="flex flex-row items-center text-sm font-semibold text-zinc-600 hover:text-zinc-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                                Clear
                                            </button>
                                        </div>
                                    </template>
                                </label>
                                <div class="relative">
                                    <!-- search input -->
                                    <input type="text" x-model="user_search"
                                        @input="search_users"
                                        @click="search_users"
                                        @click.away="user_show_results = false"
                                        placeholder="Search user by name..."
                                        class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                    <!-- search results -->
                                    <div x-show="user_show_results" x-cloak class="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <!-- not found -->
                                        <div x-cloak x-show="user_not_found" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                            <p class="text-zinc-600">No users found.</p>
                                        </div>
                                        <!-- search results -->
                                        <template x-for="user in user_search_result" :key="user.id">
                                            <div @click="select_user(user)"
                                                class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                                x-text="user.name">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endauth
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
                    </template>

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
                                <div x-cloak x-show="user_reservations.filter(id => { const reservation = reservations.find(r => r.id === id); return new Date(reservation.start_full) >= new Date(new Date().toLocaleString(undefined, {timeZone: timezone})); }).length === 0" class="col-span-full">
                                    <p class="text-zinc-600">You have no upcoming reservations.</p>
                                </div>
                                <template x-for="reservation_id in user_reservations.filter(id => { const reservation = reservations.find(r => r.id === id); return new Date(reservation.start_full) >= new Date(new Date().toLocaleString(undefined, {timeZone: timezone})); })" :key="reservation_id">
                                    <div x-data="{ reservation: reservations.find(r => r.id === reservation_id) }" class="p-4 border rounded">
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
                                                <span x-text="'Date: ' + style_date(reservation.start_full)"></span>
                                            </span>
                                            <span class="flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                <span x-text="'Time: ' + style_time(reservation.start_full)"></span>
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
                            <div x-cloak x-show="user_reservations.filter(id => { const reservation = reservations.find(r => r.id === id); return new Date(reservation.start_full) < new Date(new Date().toLocaleString(undefined, {timeZone: timezone})); }).length > 0" x-data="{ show_past: false }" class="flex flex-col gap-4">
                                <button @click="show_past = !show_past" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': show_past }">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <span x-text="show_past ? 'Hide Past Reservations' : 'Show Past Reservations'"></span>
                                </button>
                                
                                <div x-show="show_past" x-collapse class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-screen overflow-y-auto">
                                    <template x-for="reservation_id in user_reservations.filter(id => { const reservation = reservations.find(r => r.id === id); return new Date(reservation.start_full) < new Date(new Date().toLocaleString(undefined, {timeZone: timezone})); })" :key="reservation_id">
                                        <div x-data="{ reservation: reservations.find(r => r.id === reservation_id) }" class="p-4 border rounded opacity-75">
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
                                                    <span x-text="'Date: ' + style_date(reservation.start_full)"></span>
                                                </span>
                                                <span class="flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    <span x-text="'Time: ' + style_time(reservation.start_full)"></span>
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
                                    <i class="text-zinc-500 pr-1">member since:</i> <a x-text="style_date(new Date('{{ Auth::user()->created_at }}'))"></a>
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
                            timezone: '',
                            max_future_reservations: null,
                            max_days_in_advance: null,
                            durations: [],
                            opening_hours: {},
                            custom_opening_hours: {},
                            closing_dates: [],
                            tables: [],
                            reservations: [],
                            user_reservations: [],
                            get_restaurant_data() {
                                axios.post('/api/restaurant_data').then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.timezone = data.timezone;
                                        this.max_future_reservations = data.max_future_reservations;
                                        this.max_days_in_advance = data.max_days_in_advance;
                                        this.durations = data.durations;
                                        this.opening_hours = data.opening_hours;
                                        this.custom_opening_hours = data.custom_opening_hours;
                                        this.closing_dates = data.closing_dates;
                                        this.tables = data.tables;
                                        this.reservations = data.reservations.map(reservation => ({
                                            ...reservation,
                                            start_full: new Date(`${reservation.start_date} ${reservation.start_time}`),
                                            end_full: new Date(`${reservation.end_date} ${reservation.end_time}`)
                                        }));
                                        this.user_reservations = data.user_reservations;
                                        this.sort_user_reservations();
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
                            selected_date: '', //yyyy-mm-dd
                            selected_time: '', //hh:mm
                            duration: '', //in hours (e.g. 1.5 = 1 hour and 30 minutes)
                            selected_table: null, //yyyy-mm-dd
                            get min_day() {
                                let date = new Date().toLocaleString(undefined, {timeZone: this.timezone});
                                date = new Date(date);
                                return this.style_html_date(date); //yyyy-mm-dd
                            },
                            get max_day() {
                                let date = new Date().toLocaleString(undefined, {timeZone: this.timezone});
                                date = new Date(date);
                                date.setHours(0, 0, 0, 0); //matching php max_day calculation
                                date.setDate(date.getDate() + this.max_days_in_advance);
                                return this.style_html_date(date); //yyyy-mm-dd
                            },

                            //employee selection
                            selected_user: null,
                            selected_user_id: null,
                            user_search: '',
                            user_search_result: [],
                            user_show_results: false,
                            user_not_found: false,
                            search_users() {
                                if (this.user_search.length >= 2)
                                {
                                    axios.post('/api/admin/search_users', { search: this.user_search }).then(response => {
                                        this.user_search_result = response.data.users;
                                        if (this.user_search_result.length === 0) { this.user_not_found = true; }
                                        else { this.user_not_found = false; }
                                        this.user_show_results = true;
                                    });
                                } else {
                                    this.user_search_result = [];
                                    this.user_show_results = false;
                                }
                            },
                            select_user(user) {
                                this.selected_user = user;
                                this.selected_user_id = user.id;
                                this.user_search = user.name;
                                this.user_show_results = false;
                            },
                            clear_user() {
                                this.selected_user = null;
                                this.selected_user_id = null;
                                this.user_search = '';
                                this.user_show_results = false;
                            },

                            //reservation actions
                            add_to_google_calendar(reservation_id) {
                                const reservation = this.reservations.find(r => r.id === reservation_id);

                                //convert times to google calendar format
                                const start_iso = reservation.start_full.getFullYear() +
                                    String(reservation.start_full.getMonth() + 1).padStart(2, '0') +
                                    String(reservation.start_full.getDate()).padStart(2, '0') + 
                                    'T' +
                                    String(reservation.start_full.getHours()).padStart(2, '0') +
                                    String(reservation.start_full.getMinutes()).padStart(2, '0') +
                                    '00';
                                const end_iso = reservation.end_full.getFullYear() +
                                    String(reservation.end_full.getMonth() + 1).padStart(2, '0') +
                                    String(reservation.end_full.getDate()).padStart(2, '0') + 
                                    'T' +
                                    String(reservation.end_full.getHours()).padStart(2, '0') +
                                    String(reservation.end_full.getMinutes()).padStart(2, '0') +
                                    '00';
                                
                                let google_calendar_url = 'https://calendar.google.com/calendar/r/eventedit';
                                google_calendar_url += '?text=' + encodeURIComponent("Reservation at {{ config('app.name') }}");
                                google_calendar_url += '&dates=' + encodeURIComponent(start_iso + '/' + end_iso);
                                google_calendar_url += '&ctz=' + encodeURIComponent(this.timezone);
                                google_calendar_url += '&details=' + encodeURIComponent('Reservation at Table ' + reservation.table_id + '.');
                                window.open(google_calendar_url, '_blank');
                            },
                            cancel_reservation(reservation_id) {
                                axios.post('/api/delete_reservation', { id: reservation_id }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.user_reservations = this.user_reservations.filter(id => id !== reservation_id);
                                        this.reservations = this.reservations.filter(reservation => reservation.id !== reservation_id);
                                    }
                                    else
                                    {
                                        Alpine.store('modal').open(
                                            'Error',
                                            'Failed to cancel reservation. Please try refreshing the page. Client error.',
                                            null,
                                            'OK',
                                            'Cancel',
                                            'bg-rose-600 hover:bg-rose-700'
                                        );
                                    }
                                }).catch(error => {
                                    Alpine.store('modal').open(
                                        'Error',
                                        'Failed to cancel reservation. Please try refreshing the page. Server error.',
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
                                
                                const close_hour = parseInt(close);
                                const close_minutes = close.includes(':') ? parseInt(close.split(':')[1]) : 0;
                                
                                let hours_until_close;
                                if (close_hour < selected_hour) //selected time is before midnight, closing is after
                                {
                                    hours_until_close = (24 - selected_hour) + close_hour;
                                    if (close_minutes === 30) { hours_until_close += 0.5; }
                                    if (selected_minutes === 30) { hours_until_close -= 0.5; }
                                }
                                else if (selected_hour < close_hour && close_hour < open) //selected time is after midnight
                                {
                                    hours_until_close = close_hour - selected_hour;
                                    if (close_minutes === 30) { hours_until_close += 0.5; }
                                    if (selected_minutes === 30) { hours_until_close -= 0.5; }
                                }
                                else //normal case
                                {
                                    hours_until_close = close_hour - selected_hour;
                                    if (close_minutes === 30) { hours_until_close += 0.5; }
                                    if (selected_minutes === 30) { hours_until_close -= 0.5; }
                                }
                                
                                return this.durations.filter(duration => duration <= hours_until_close);
                            },
                            get available_tables() {
                                if (!this.selected_date || !this.selected_time || !this.duration || this.closing_dates.includes(this.selected_date) || this.closing_dates.includes(new Date(this.selected_date).toLocaleDateString('en-US', {weekday: 'long'}))) { return []; }

                                const new_start = new Date(`${this.selected_date} ${this.selected_time}`);
                                const new_end = new Date(new_start.getTime() + this.duration * 60 * 60 * 1000);
                                const reserved_tables = this.reservations.filter(reservation => {
                                    const res_start = new Date(reservation.start_full);
                                    const res_end = new Date(reservation.end_full);

                                    //check for any overlap
                                    return (
                                        (res_end > new_start && res_end <= new_end) || //case 1
                                        (res_start >= new_start && res_start < new_end) || //case 2
                                        (res_start <= new_start && res_end >= new_end) //case 3
                                    );
                                }).map(reservation => reservation.table_id);

                                return this.tables.filter(table => !reserved_tables.includes(table.id));
                            },

                            //helpers
                            style_date(dt) { return dt.getDate().toString().padStart(2, '0') + '-' + (dt.getMonth() + 1).toString().padStart(2, '0') + '-' + dt.getFullYear(); },
                            style_time(dt) { return dt.getHours().toString().padStart(2, '0') + ':' + dt.getMinutes().toString().padStart(2, '0'); },
                            style_html_date(dt) { return dt.getFullYear() + '-' + (dt.getMonth() + 1).toString().padStart(2, '0') + '-' + dt.getDate().toString().padStart(2, '0'); },
                            parse_duration(duration) {
                                let result = '';
                                let hours = Math.floor(duration);
                                let minutes = Math.floor((duration - hours) * 60);
                                if (hours > 0) { result += hours + ' hour' + (hours > 1 ? 's' : ''); }
                                if (minutes > 0) { result += ' ' + minutes + ' minutes'; }
                                return result;
                            },
                            sort_user_reservations() {
                                //sort user reservations by date and time
                                this.user_reservations.sort((a, b) => {
                                    a = this.reservations.find(reservation => reservation.id === a);
                                    b = this.reservations.find(reservation => reservation.id === b);
                                    let dateA = new Date(a.start_full);
                                    let dateB = new Date(b.start_full);
                                    let endA = new Date(a.end_full);
                                    let endB = new Date(b.end_full);

                                    //if opening hours are 16:00-02:00 and reservation is at 01:00. it should be on the next date
                                    //but we use still the previous date so it is tied to the previous date opening hours
                                    //so we need to check if the reservation should be on the next date and if so, we use the next date
                                    //find out by getting the opening hours
                                    const custom_hours = this.custom_opening_hours[this.style_html_date(dateA)];
                                    const day = dateA.getDay();
                                    const { close } = custom_hours || this.opening_hours[day];
                                    const { open } = custom_hours || this.opening_hours[day];

                                    const opening_hour = parseInt(open);
                                    const opening_minutes = open.includes(':') ? parseInt(open.split(':')[1]) : 0;
                                    if (parseInt(a.start_time.split(':')[0]) < opening_hour || (parseInt(a.start_time.split(':')[0]) === opening_hour && parseInt(a.start_time.split(':')[1]) < opening_minutes))
                                    {
                                        dateA = new Date(dateA.setDate(dateA.getDate() + 1));
                                    }
                                    if (parseInt(b.start_time.split(':')[0]) < opening_hour || (parseInt(b.start_time.split(':')[0]) === opening_hour && parseInt(b.start_time.split(':')[1]) < opening_minutes))
                                    {
                                        dateB = new Date(dateB.setDate(dateB.getDate() + 1));
                                    }

                                    if (dateA - dateB === 0)
                                    {
                                        return endA - endB;
                                    }
                                    return dateA - dateB;
                                });
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
                                    table_id: this.selected_table,
                                    ...(this.selected_user_id ? { user_id: this.selected_user_id } : {})
                                }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        //if user is not logged in, we never get here and this prevents error on getting user id from Auth::user() on page load
                                        @auth
                                        const reservation = {
                                            ...data.reservation,
                                            start_full: new Date(`${data.reservation.start_date} ${data.reservation.start_time}`),
                                            end_full: new Date(`${data.reservation.end_date} ${data.reservation.end_time}`)
                                        };
                                        //insert into local variables
                                        this.reservations.push(reservation);
                                        //insert into user reservations only if for current user
                                        if (this.selected_user_id === null || this.selected_user_id === {{ Auth::user()->id }})
                                        {
                                            this.user_reservations.push(reservation.id);
                                            this.sort_user_reservations();
                                        }
                                        @endauth
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
                                    console.log(error);
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
                                    this.clear_user();
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