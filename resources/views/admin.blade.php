<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-auto">

<head>
    <title>{{ config('app.name') }} - Employees</title>
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
                <div x-data="admin_data" class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-y-8">

                    <!-- reservations -->
                    <div x-init="get_reservations" class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-6">
                            <h2 class="text-2xl font-bold tracking-wide">Reservations</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>
                        <div class="flex flex-col gap-y-4">
                            <!-- date selector -->
                            <div class="flex flex-row gap-2">
                                <input x-model="date" :min="today" type="date" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                <button @click="get_reservations" class="flex relative items-center gap-2 bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Update
                                </button>
                            </div>
                            <!-- reservations for selected date -->
                            <div x-cloak x-show="reservations_loading" class="flex flex-col items-center justify-center">
                                <p class="text-zinc-500">Loading reservations...</p>
                            </div>
                            <div x-cloak x-show="reservations_error" class="flex flex-col items-center justify-center">
                                <p class="text-zinc-500">Failed to load reservations. Please try again.</p>
                            </div>
                            <div x-cloak x-show="reservations_loaded && reservations.length === 0" class="flex flex-col items-center justify-center">
                                <p class="text-zinc-500">No reservations found for this date.</p>
                            </div>
                            <template x-if="reservations_loaded && reservations.length > 0">
                                <div class="overflow-auto max-h-screen">
                                    <table class="min-w-full divide-y divide-zinc-200">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Start</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">End</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Table</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Warnings</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-zinc-200">
                                            <template x-for="(reservation, index) in sorted_reservations" :key="reservation.id">
                                                <tr class="hover:bg-zinc-200" :class="{ 
                                                    'bg-white': reservation.status === 'upcoming',
                                                    'bg-rose-50': reservation.status === 'ongoing',
                                                    'bg-rose-100': reservation.status === 'past'
                                                }">
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.time"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.end_time"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.user.name"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="(reservation.table.name ?? ('Table ' + reservation.table_id)) + ' (' + reservation.seats + ' seats)'"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-yellow-500" x-text="reservation.seats !== reservation.table.seats ? 'Table has only ' + reservation.table.seats + ' seats' : ''"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <button @click="cancel_reservation(reservation.id)" class="text-rose-500 hover:text-rose-700">Cancel</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- user lookup -->
                    <template x-cloak x-if="reservations_loaded">
                        <div x-init="select_user({id: {{ Auth::user()->id }}, name: '{{ Auth::user()->name }}'})" class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">User Lookup</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <div class="flex flex-col gap-y-4 min-h-80">
                                <!-- user selection -->
                                <div class="flex flex-col">
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
                                <!-- user data -->
                                <div class="flex flex-col gap-y-4">
                                    <template x-cloak x-if="user_data">
                                        <div class="flex flex-col">
                                            <label class="block text-zinc-700 text-sm font-bold mb-1 flex flex-row items-center gap-x-2">User Data</label>
                                            <div class="flex flex-col">
                                                <p class="text-zinc-600">Name: <span class="text-zinc-700" x-text="user_data.name"></span></p>
                                                <p class="text-zinc-600">Email: <span class="text-zinc-700" x-text="user_data.email"></span></p>
                                                <p class="text-zinc-600">Member since: <span class="text-zinc-700" x-text="new Date(user_data.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false})"></span></p>
                                            </div>
                                        </div>
                                    </template>
                                    <!-- user reservations -->
                                    <template x-cloak x-if="user_data">
                                        <div class="flex flex-col gap-y-4">
                                            <!-- future reservations -->
                                            <div x-data="{ show_future: false }" class="flex flex-col gap-4">
                                                <button @click="show_future = !show_future" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': show_future }">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                    <span x-text="show_future ? 'Hide Future Reservations' : 'Show Future Reservations'"></span>
                                                </button>
                                                
                                                <div x-show="show_future" x-collapse class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div x-cloak x-show="user_reservations.filter(r => new Date(r.date) >= new Date().setHours(0,0,0,0)).length === 0">
                                                        <p class="text-zinc-600">No upcoming reservations found.</p>
                                                    </div>
                                                    <template x-for="reservation in user_reservations.filter(r => new Date(r.date) >= new Date().setHours(0,0,0,0))" :key="reservation.id">
                                                        <div class="p-4 border rounded">
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
                                                                <div class="mt-2">
                                                                    <button @click="cancel_reservation(reservation.id)" class="text-sm font-semibold text-rose-600 hover:text-rose-700 flex flex-row items-center gap-1">
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
                                            </div>
                                            <!-- past reservations -->
                                            <div x-data="{ show_past: false }" class="flex flex-col gap-4">
                                                <button @click="show_past = !show_past" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': show_past }">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                    <span x-text="show_past ? 'Hide Past Reservations' : 'Show Past Reservations'"></span>
                                                </button>
                                                
                                                <div x-show="show_past" x-collapse class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div x-cloak x-show="user_reservations.filter(r => new Date(r.date) < new Date().setHours(0,0,0,0)).length === 0">
                                                        <p class="text-zinc-600">No past reservations found.</p>
                                                    </div>
                                                    <template x-for="reservation in user_reservations.filter(r => new Date(r.date) < new Date().setHours(0,0,0,0))" :key="reservation.id">
                                                        <div class="p-4 border rounded opacity-75">
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
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    @include('modal')
                </div>

                <script>
                    function admin_data() {
                        return {
                            //user lookup
                            selected_user: null,
                            selected_user_id: null,
                            user_search: '',
                            user_search_result: [],
                            user_show_results: false,
                            user_not_found: false,
                            user_data: null,
                            user_reservations: [],
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
                                this.get_user_data(user.id);
                            },
                            clear_user() {
                                this.selected_user = null;
                                this.selected_user_id = null;
                                this.user_search = '';
                                this.user_show_results = false;
                                this.user_data = null;
                            },
                            get_user_data(user_id) {
                                axios.post('/api/admin/user_data', { id: user_id }).then(response => {
                                    this.user_data = response.data.user;
                                    this.user_reservations = response.data.reservations;
                                    this.sort_user_reservations();
                                }).catch(error => {
                                    Alpine.store('modal').open(
                                        'Error',
                                        'Failed to get user data. Server error.',
                                        null,
                                        'OK',
                                        'Cancel',
                                        'bg-rose-600 hover:bg-rose-700'
                                    );
                                });
                            },

                            //reservations
                            today: new Date().toLocaleDateString('en-CA'),
                            date: new Date().toLocaleDateString('en-CA'),
                            reservations: [],
                            reservations_loading: false,
                            reservations_loaded: false,
                            reservations_error: false,
                            tables: [],
                            get sorted_reservations() {
                                let sorted = this.reservations.map(reservation => {
                                    const now = new Date();
                                    const start = new Date(`${reservation.date}T${reservation.time}`);
                                    const end = new Date(start.getTime() + (reservation.duration * 3600 * 1000));
                                    let status = 'upcoming';
                                    if (start <= now && end > now) { status = 'ongoing'; }
                                    if (end <= now) { status = 'past'; }
                                    return {...reservation, status, start_time: start};
                                }).sort((a, b) => {
                                    //first sort by status into groups
                                    const status = {upcoming: 0, ongoing: 1, past: 2};
                                    if (status[a.status] !== status[b.status]) { return status[a.status] - status[b.status]; }
                                }).sort((a, b) => {
                                    //then sort each group by start time and if equal by end time
                                    if (a.status === b.status)
                                    {
                                        if (a.start_time.getTime() === b.start_time.getTime())
                                        {
                                            const a_end = new Date(a.start_time.getTime() + (a.duration * 3600 * 1000));
                                            const b_end = new Date(b.start_time.getTime() + (b.duration * 3600 * 1000));
                                            return a_end - b_end;
                                        }
                                        return a.start_time - b.start_time;
                                    }
                                    return 0;
                                });
                                return sorted;
                            },
                            get_reservations() {
                                this.reservations_loading = true;
                                this.reservations_loaded = false;
                                this.reservations_error = false;
                                axios.post('/api/admin/reservations', { date: this.date }).then(response => {
                                    this.reservations = response.data.reservations.map(reservation => ({
                                        ...reservation,
                                        time: reservation.time.substring(0, 5),
                                        end_time: new Date(new Date(`${reservation.date}T${reservation.time}`).getTime() + (reservation.duration * 3600 * 1000)).toLocaleTimeString('en-CA', { hour: '2-digit', minute: '2-digit', hour12: false })
                                    }));
                                    this.tables = response.data.tables;
                                    this.reservations_loaded = true;
                                }).catch(error => {
                                    this.reservations_error = true;
                                }).finally(() => {
                                    this.reservations_loading = false;
                                });
                            },
                            cancel_reservation(reservation_id) {
                                Alpine.store('modal').open(
                                    'Are you sure?',
                                    'This action cannot be undone.',
                                    () => {
                                        axios.post('/api/delete_reservation', { id: reservation_id }).then(response => {
                                            const data = response.data;
                                            if (data.success)
                                            {
                                                //remove from reservations
                                                this.reservations = this.reservations.filter(reservation => reservation.id !== reservation_id);
                                                //remove from user reservations
                                                if (this.user_reservations.find(reservation => reservation.id === reservation_id))
                                                {
                                                    this.user_reservations = this.user_reservations.filter(reservation => reservation.id !== reservation_id);
                                                    this.sort_user_reservations();
                                                }
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
                                    'Confirm',
                                    'Cancel',
                                    'bg-rose-600 hover:bg-rose-700'
                                );
                            },

                            //helpers
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
                                    a = this.tables.find(table => table.id === a.table_id);
                                    b = this.tables.find(table => table.id === b.table_id);
                                    const dateA = new Date(`${a.date} ${a.time}`);
                                    const dateB = new Date(`${b.date} ${b.time}`);
                                    return dateA - dateB;
                                });
                            },
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</body>

</html>