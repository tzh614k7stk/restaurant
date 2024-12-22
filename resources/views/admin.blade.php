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
    @vite('resources/js/app.js')
    @vite('resources/js/axios.js')

    @livewireStyles
    @livewireScripts
</head>

<body class="bg-gradient-to-b from-zinc-50 to-zinc-300 text-zinc-900">
    <div class="main-container">
        @include('navbar')

        <div class="min-h-screen px-3 leading-relaxed">
            <div class="container py-16 mx-auto">
                <div x-data="admin_data" x-init="get_restaurant_config" class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-y-8">

                    <!-- reservations -->
                    <template x-cloak x-if="first_load">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">Reservations</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                </svg>
                            </div>
                            <div class="flex flex-col gap-y-4">
                                <!-- date selector for filtering reservations -->
                                <div class="flex flex-row gap-2">
                                    <input x-model="date" :min="today" type="date" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                    <button @click="get_reservations" class="flex relative items-center gap-2 bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Update
                                    </button>
                                </div>
                                <!-- loading states -->
                                <div x-cloak x-show="reservations_loading" class="flex flex-col items-center justify-center">
                                    <p class="text-zinc-500">Loading reservations...</p>
                                </div>
                                <div x-cloak x-show="reservations_error" class="flex flex-col items-center justify-center">
                                    <p class="text-zinc-500">Failed to load reservations. Please try again.</p>
                                </div>
                                <div x-cloak x-show="reservations_loaded && reservations.length === 0" class="flex flex-col items-center justify-center">
                                    <p class="text-zinc-500">No reservations found for this date.</p>
                                </div>
                                <!-- table with reservations for selected date -->
                                <template x-if="reservations_loaded && reservations.length > 0">
                                    <div class="overflow-auto">
                                        <table class="min-w-full divide-y divide-zinc-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Start</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">End</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Table</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Warnings</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Note</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-zinc-200">
                                                <!-- reservation sorted by status -->
                                                <template x-for="(reservation, index) in sorted_reservations" :key="reservation.id">
                                                    <tr class="hover:bg-zinc-200" :class="{ 
                                                        'bg-white': reservation.status === 'upcoming',
                                                        'bg-rose-50': reservation.status === 'ongoing',
                                                        'bg-rose-100': reservation.status === 'past'
                                                    }">
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="style_time(reservation.start_full)"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="style_time(reservation.end_full)"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.user.name"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="(reservation.table.name ?? ('Table ' + reservation.table_id)) + ' (' + reservation.seats + ' seats)'"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-yellow-500" x-text="reservation.seats !== reservation.table.seats ? 'Table has only ' + reservation.table.seats + ' seats' : ''"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.note"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap flex flex-col">
                                                            <button @click="edit_reservation_note(reservation.id, reservation.note)" class="text-zinc-600 hover:text-zinc-800">Edit Note</button>
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
                    </template>

                    <!-- user lookup -->
                    <template x-cloak x-if="reservations_loaded">
                        <div x-init="select_user({id: {{ Auth::user()->id }}, name: '{{ Auth::user()->name }}'})" class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">User Lookup</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <div class="flex flex-col gap-y-4 min-h-80">
                                <!-- user selection -->
                                <div class="flex flex-col">
                                    <!-- label with selected user info -->
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
                                    <!-- search input with dropdown results -->
                                    <div class="relative">
                                        <!-- search input -->
                                        <input type="text" x-model="user_search"
                                            @input="search_users"
                                            @click="search_users"
                                            @click.away="user_show_results = false"
                                            placeholder="Search user by name..."
                                            class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                        <!-- dropdown with search results -->
                                        <div x-show="user_show_results" x-cloak class="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <!-- no results found message -->
                                            <div x-cloak x-show="user_not_found" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                                <p class="text-zinc-600">No users found.</p>
                                            </div>
                                            <!-- list of found users -->
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
                                            <!-- user details display -->
                                            <div class="flex flex-col">
                                                <p class="text-zinc-600">Name: <span class="text-zinc-700" x-text="user_data.name"></span></p>
                                                <p class="text-zinc-600">Employee: <span class="text-zinc-700" x-text="user_data.employee ? 'Yes' : 'No'"></span></p>
                                                <p class="text-zinc-600">Email: <span class="text-zinc-700" x-text="user_data.email"></span></p>
                                                <p class="text-zinc-600">Member since: <span class="text-zinc-700" x-text="style_human_date(new Date(user_data.created_at)) + ' ' + style_time(new Date(user_data.created_at))"></span></p>
                                                <p class="text-zinc-600">Reservation Limit: <span class="text-zinc-700" x-text="user_data.max_future_reservations !== null ? (user_data.max_future_reservations !== 0 ? user_data.max_future_reservations : 'Blocked') : max_future_reservations"></span></p>
                                                <p x-cloak x-show="user_data.note" class="text-zinc-600">Note: <span class="text-zinc-700" x-text="user_data.note"></span></p>
                                            </div>
                                            <!-- user action buttons -->
                                            <button @click="edit_user_note(user_data.id, user_data.note)" class="text-sm font-semibold text-zinc-600 hover:text-zinc-700 flex flex-row items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                Edit Note
                                            </button>
                                            <!-- conditional buttons based on user status -->
                                            <div x-cloak x-show="user_data.max_future_reservations !== 0" class="flex flex-col">
                                                <button @click="set_max_future_reservations_with_input(user_data.id, user_data.max_future_reservations)" class="text-sm font-semibold text-zinc-600 hover:text-zinc-700 flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                    Change Reservation Limit
                                                </button>
                                                <button @click="set_max_future_reservations(user_data.id, 0)" class="text-sm font-semibold text-rose-500 hover:text-rose-700 flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    Block User
                                                </button>
                                            </div>
                                            <!-- unblock button for blocked users -->
                                            <div x-cloak x-show="user_data.max_future_reservations === 0" class="flex flex-col">
                                                <button @click="set_max_future_reservations(user_data.id, null)" class="text-sm font-semibold text-green-300 hover:text-green-400 flex flex-row items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    Unblock User
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <!-- user reservations -->
                                    <template x-cloak x-if="user_data">
                                        <div class="flex flex-col gap-y-4">
                                            <!-- future reservations collapsible section -->
                                            <div x-data="{ show_future: false }" class="flex flex-col gap-4">
                                                <button @click="show_future = !show_future" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': show_future }">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                    <span x-text="show_future ? 'Hide Future Reservations' : 'Show Future Reservations'"></span>
                                                </button>
                                                
                                                <!-- grid of future reservations -->
                                                <div x-show="show_future" x-collapse class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div x-cloak x-show="get_future_reservations(user_reservations).length === 0">
                                                        <p class="text-zinc-600">No upcoming reservations found.</p>
                                                    </div>
                                                    <template x-for="reservation in get_future_reservations(user_reservations)" :key="reservation.id">
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
                                                                    <span x-text="'Date: ' + style_human_date(reservation.start_full)"></span>
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
                                                                <span x-cloak x-show="reservation.note" class="flex flex-row items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                                    </svg>
                                                                    <span x-text="'Note: ' + reservation.note"></span>
                                                                </span>
                                                                <div class="mt-2 flex flex-col gap-y-1">
                                                                    <button @click="edit_reservation_note(reservation.id, reservation.note)" class="text-sm font-semibold text-zinc-600 hover:text-zinc-700 flex flex-row items-center gap-1">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                                        </svg>
                                                                        Edit Note
                                                                    </button>
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
                                            <!-- past reservations collapsible section -->
                                            <div x-data="{ show_past: false }" class="flex flex-col gap-4">
                                                <button @click="show_past = !show_past" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': show_past }">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                    <span x-text="show_past ? 'Hide Past Reservations' : 'Show Past Reservations'"></span>
                                                </button>
                                                
                                                <!-- grid of past reservations -->
                                                <div x-show="show_past" x-collapse class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div x-cloak x-show="get_past_reservations(user_reservations).length === 0">
                                                        <p class="text-zinc-600">No past reservations found.</p>
                                                    </div>
                                                    <template x-for="reservation in get_past_reservations(user_reservations)" :key="reservation.id">
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
                                                                    <span x-text="'Date: ' + style_human_date(reservation.start_full)"></span>
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
                                                                <span x-cloak x-show="reservation.note" class="flex flex-row items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                                    </svg>
                                                                    <span x-text="'Note: ' + reservation.note"></span>
                                                                </span>
                                                                <div class="mt-2 flex flex-col gap-y-1">
                                                                    <button @click="edit_reservation_note(reservation.id, reservation.note)" class="text-sm font-semibold text-zinc-600 hover:text-zinc-700 flex flex-row items-center gap-1">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                                        </svg>
                                                                        Edit Note
                                                                    </button>
                                                                </div>
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

                    <!-- opening hours -->
                    <template x-cloak x-if="reservations_loaded">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">Opening Hours</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <p x-cloak x-show="Object.keys(opening_hours).length === 0" class="text-zinc-600">No opening hours currently scheduled.</p>
                            <div x-cloak x-show="Object.keys(opening_hours).length > 0" class="overflow-auto">
                                <table class="min-w-full divide-y divide-zinc-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Day</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Opening Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Closing Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-zinc-200">
                                        <!-- loop through days of week -->
                                        <template x-for="(day_name, index) in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']" :key="index">
                                            <tr class="hover:bg-zinc-100">
                                                <td class="px-6 py-4 whitespace-nowrap" x-text="day_name"></td>
                                                <!-- opening time input, hidden if day is closed -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="time" 
                                                        x-model="opening_hours[(index + 1) % 7].open"
                                                        :hidden="closing_dates.includes(day_name)"
                                                        class="bg-white shadow appearance-none border rounded py-1 px-2 text-zinc-700 leading-tight focus:outline-none">
                                                </td>
                                                <!-- closing time input, hidden if day is closed -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="time" 
                                                        x-model="opening_hours[(index + 1) % 7].close"
                                                        :hidden="closing_dates.includes(day_name)"
                                                        class="bg-white shadow appearance-none border rounded py-1 px-2 text-zinc-700 leading-tight focus:outline-none">
                                                </td>
                                                <!-- toggle button for open/closed status -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <button @click="toggle_day_status((index + 1) % 7, day_name)"
                                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                        :class="closing_dates.includes(day_name) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                                        x-text="closing_dates.includes(day_name) ? 'Closed' : 'Open'">
                                                    </button>
                                                </td>
                                                <!-- save button -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <button @click="save_opening_hours((index + 1) % 7, day_name)" class="text-zinc-600 hover:text-zinc-800 hover:font-bold">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- special hours -->
                    <template x-cloak x-if="reservations_loaded">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center gap-2 mb-6">
                                    <h2 class="text-2xl font-bold tracking-wide">Special Hours</h2>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>

                                <!-- add new special hours -->
                                <div class="flex flex-col gap-4">
                                    <div x-data="{ new_custom_hours: false }" class="flex flex-col gap-2">
                                        <!-- toggle button for new special hours form -->
                                        <button @click="new_custom_hours = !new_custom_hours" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': new_custom_hours }">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                            Add New Custom Hours
                                        </button>
                                        <!-- collapsible form for new special hours -->
                                        <div x-show="new_custom_hours" x-collapse class="flex flex-col gap-2 p-4 border rounded">
                                            <label class="text-sm font-semibold text-zinc-600">Date</label>
                                            <input type="date" x-model="new_special_date" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <label class="text-sm font-semibold text-zinc-600">Opening Time</label>
                                            <input type="time" x-model="new_special_open" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <label class="text-sm font-semibold text-zinc-600">Closing Time</label>
                                            <input type="time" x-model="new_special_close" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <button @click="save_special_hours()" class="flex relative items-center justify-center gap-2 bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                Set Special Hours
                                            </button>
                                            <button @click="save_special_hours(true)" class="flex relative items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Mark as Closed
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- list of special hours -->
                                <div class="overflow-auto">
                                    <!-- show message if no special hours -->
                                    <template x-if="get_sorted_special_dates().length === 0">
                                        <p class="text-zinc-600">No special hours currently scheduled.</p>
                                    </template>
                                    <!-- show table if special hours exist -->
                                    <template x-if="get_sorted_special_dates().length > 0">
                                        <table class="min-w-full divide-y divide-zinc-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Opening Time</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Closing Time</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-zinc-200">
                                                <!-- loop through special hours and closed dates -->
                                                <template x-for="date in get_sorted_special_dates()" :key="date">
                                                    <tr class="hover:bg-zinc-100">
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="style_human_date(new Date(date))"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="date in custom_opening_hours ? custom_opening_hours[date].open : '-'"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap" x-text="date in custom_opening_hours ? custom_opening_hours[date].close : '-'"></td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                                :class="date in custom_opening_hours ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                                x-text="date in custom_opening_hours ? 'Special Hours' : 'Closed'">
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <button @click="delete_special_hours(date)" class="text-rose-500 hover:text-rose-700">
                                                                Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- tables -->
                    <template x-cloak x-if="reservations_loaded">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center gap-2 mb-6">
                                    <h2 class="text-2xl font-bold tracking-wide">Tables</h2>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                </div>
                                <!-- collapsible form to add new table -->
                                <div class="flex flex-col gap-4">
                                    <div x-data="{ new_table: false }" class="flex flex-col gap-2">
                                        <button @click="new_table = !new_table" class="flex items-center gap-2 text-zinc-600 hover:text-zinc-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': new_table }">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                            Add New Table
                                        </button>
                                        <!-- new table input form -->
                                        <div x-show="new_table" x-collapse class="flex flex-col gap-2 p-4 border rounded">
                                            <label class="text-sm font-semibold text-zinc-600">Table Name</label>
                                            <input type="text" x-model="new_table_name" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <label class="text-sm font-semibold text-zinc-600">Number of Seats</label>
                                            <input type="number" min="0" step="1" x-model="new_table_seats" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <button @click="save_table()" class="flex relative items-center justify-center gap-2 bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                Create Table
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- list of tables -->
                                <p x-cloak x-show="tables.length === 0" class="text-zinc-600">No tables available.</p>
                                <div x-cloak x-show="tables.length > 0" class="overflow-auto">
                                    <table class="min-w-full divide-y divide-zinc-200">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Seats</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <!-- table rows with editable inputs -->
                                        <tbody class="bg-white divide-y divide-zinc-200">
                                            <template x-for="table in tables" :key="table.id">
                                                <tr class="hover:bg-zinc-200">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="text" x-model="table.name" :placeholder="table.name ?? ('Table ' + table.id)" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="number" min="0" step="1" x-model="table.seats" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap flex flex-col items-end">
                                                        <button @click="delete_table(table.id)" class="text-rose-500 hover:text-rose-700">Delete</button>
                                                        <button @click="edit_table(table.id)" class="text-zinc-500 hover:text-zinc-700">Edit</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- durations -->
                    <template x-cloak x-if="reservations_loaded">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center gap-2 mb-6">
                                    <h2 class="text-2xl font-bold tracking-wide">Available Durations for Reservations</h2>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                    </svg>
                                </div>
                                <!-- new duration input -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-semibold text-zinc-600">New Duration (in hours) - only whole hours or half hours are allowed (e.g. 0.5, 2, 2.5)</label>
                                    <div class="flex flex-row gap-2">
                                        <input x-model="new_duration" type="number" step="0.5" min="0.5" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                        <button @click="create_duration(new_duration)" class="flex relative items-center gap-2 bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Create
                                        </button>
                                    </div>
                                </div>
                                <!-- duration list -->
                                <p x-cloak x-show="durations.length === 0" class="text-zinc-600">No durations available.</p>
                                <div x-cloak x-show="durations.length > 0" class="overflow-auto">
                                    <table class="min-w-full divide-y divide-zinc-200">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Duration</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-zinc-200">
                                            <template x-for="duration in durations" :key="duration">
                                                <tr class="hover:bg-zinc-200">
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="parse_duration(duration)"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap flex flex-col items-end">
                                                        <button @click="delete_duration(duration)" class="text-rose-500 hover:text-rose-700">Delete</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    @if (auth()->user()->employee->admin)
                    <!-- admin manage employees -->
                    <template x-cloak x-if="reservations_loaded">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex flex-col gap-4 min-h-80">
                                <div class="flex items-center gap-2 mb-6">
                                    <h2 class="text-2xl font-bold tracking-wide">Manage Employees</h2>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                    </svg>
                                </div>
                                <!-- new employee -->
                                <div class="flex flex-col">
                                    <label class="block text-zinc-700 text-sm font-bold mb-1">Add New Employee</label>
                                    <div class="relative">
                                        <!-- search input with dropdown trigger -->
                                        <input type="text" x-model="employee_search"
                                            @input="search_employees"
                                            @click="search_employees"
                                            @click.away="employee_show_results = false"
                                            placeholder="Search users..."
                                            class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                        <!-- dropdown results -->
                                        <div x-show="employee_show_results" x-cloak class="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <!-- no results message -->
                                            <div x-cloak x-show="employee_not_found" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                                <p class="text-zinc-600">No employees found.</p>
                                            </div>
                                            <!-- search results -->
                                            <template x-for="employee in employee_search_result" :key="employee.id">
                                                <div @click="add_employee(employee.id)"
                                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                                    x-text="employee.name">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <!-- employee list -->
                                <p x-cloak x-show="employees.length === 0" class="text-zinc-600">No employees available.</p>
                                <div x-cloak x-show="employees.length > 0" class="overflow-auto">
                                    <table class="min-w-full divide-y divide-zinc-200">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Admin</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-zinc-200">
                                            <template x-for="employee in employees" :key="employee.id">
                                                <tr class="hover:bg-zinc-200">
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="employee.name"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="employee.email"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap" x-text="employee.admin ? 'Yes' : 'No'"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap flex flex-col items-end">
                                                        <button @click="remove_employee(employee.id)" class="text-rose-500 hover:text-rose-700">Remove</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- admin manage config -->
                    <template x-cloak x-if="reservations_loaded">
                        <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8 max-h-screen overflow-y-auto">
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">Restaurant Configuration</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>

                            <div class="flex flex-col gap-6">
                                <!-- reservation settings -->
                                <div class="flex flex-col gap-4">
                                    <h3 class="text-lg font-semibold">Reservation Settings</h3>
                                    
                                    <!-- minimum hours setting -->
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-semibold text-zinc-600">Minimum Hours Before Reservation</label>
                                        <div class="flex gap-2">
                                            <input type="number" x-model="min_hours_before_reservation" min="0" step="1" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <button @click="save_config('min_hours_before_reservation', min_hours_before_reservation)" class="flex items-center gap-2 bg-zinc-700 hover:bg-zinc-800 text-white font-bold rounded px-4">
                                                Save
                                            </button>
                                        </div>
                                        <p class="text-sm text-zinc-500">Minimum number of hours before a reservation can be made</p>
                                    </div>

                                    <!-- max future reservations setting -->
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-semibold text-zinc-600">Maximum Future Reservations (per user)</label>
                                        <div class="flex gap-2">
                                            <input type="number" x-model="max_future_reservations" min="0" step="1" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <button @click="save_config('max_future_reservations', max_future_reservations)" class="flex items-center gap-2 bg-zinc-700 hover:bg-zinc-800 text-white font-bold rounded px-4">
                                                Save
                                            </button>
                                        </div>
                                        <p class="text-sm text-zinc-500">Maximum number of future reservations a user can have at once</p>
                                    </div>

                                    <!-- max days in advance setting -->
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-semibold text-zinc-600">Maximum Days in Advance</label>
                                        <div class="flex gap-2">
                                            <input type="number" x-model="max_days_in_advance" min="0" step="1" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                            <button @click="save_config('max_days_in_advance', max_days_in_advance)" class="flex items-center gap-2 bg-zinc-700 hover:bg-zinc-800 text-white font-bold rounded px-4">
                                                Save
                                            </button>
                                        </div>
                                        <p class="text-sm text-zinc-500">Maximum number of days in advance a reservation can be made</p>
                                    </div>
                                </div>

                                <!-- contact information -->
                                <div class="flex flex-col gap-4">
                                    <h3 class="text-lg font-semibold">Contact Information</h3>
                                    
                                    <!-- email setting -->
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-semibold text-zinc-600">Email Address</label>
                                        <div class="flex gap-2">
                                            <input type="text" x-model="email" maxlength="128" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none w-full max-w-md">
                                            <button @click="save_config('email', email)" class="flex items-center gap-2 bg-zinc-700 hover:bg-zinc-800 text-white font-bold rounded px-4">
                                                Save
                                            </button>
                                        </div>
                                    </div>

                                    <!-- phone setting -->
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-semibold text-zinc-600">Phone Number</label>
                                        <div class="flex gap-2">
                                            <input type="text" x-model="phone" maxlength="128" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none w-full max-w-md">
                                            <button @click="save_config('phone', phone)" class="flex items-center gap-2 bg-zinc-700 hover:bg-zinc-800 text-white font-bold rounded px-4">
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    @endif

                    @include('modal')
                </div>

                <script>
                    function admin_data() {
                        return {                            
                            //server configuration
                            first_load: false,
                            tables: [],
                            timezone: '',
                            durations: [],
                            opening_hours: {},
                            custom_opening_hours: {},
                            closing_dates: [],
                            max_future_reservations: null,
                            min_hours_before_reservation: null,
                            max_days_in_advance: null,
                            phone: null,
                            email: null,
                            get_restaurant_config() {
                                axios.post('/api/admin/config').then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.timezone = data.timezone;
                                        this.opening_hours = data.opening_hours;
                                        this.custom_opening_hours = data.custom_opening_hours;
                                        this.closing_dates = data.closing_dates;
                                        this.durations = data.durations;
                                        this.employees = data.employees;
                                        this.max_future_reservations = data.max_future_reservations;
                                        this.min_hours_before_reservation = data.min_hours_before_reservation;
                                        this.max_days_in_advance = data.max_days_in_advance;
                                        this.phone = data.phone;
                                        this.email = data.email;
                                        this.tables = data.tables;
                                        if (this.date === null) { this.date = this.today; }
                                        this.get_reservations();
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to get restaurant configuration.', {error});
                                }).finally(() => {
                                    this.first_load = true; //fix min/max attribute causing date input to flicker
                                });
                            },
                            save_config(key, value) {
                                axios.post('/api/admin/save_config', { key, value }).then(response => {
                                    const data = response.data;
                                    if (!data.success) { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to save configuration.', {error});
                                });
                            },

                            //tables
                            new_table_name: '',
                            new_table_seats: null,
                            save_table() {
                                axios.post('/api/admin/save_table', { name: this.new_table_name, seats: this.new_table_seats }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.tables.push(data.table);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to save table.', {error});
                                }).finally(() => {
                                    this.new_table_name = '';
                                    this.new_table_seats = null;
                                });
                            },
                            delete_table(table_id) {
                                axios.post('/api/admin/delete_table', { id: table_id }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.tables = this.tables.filter(table => table.id !== table_id);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to delete table.', {error});
                                });
                            },
                            edit_table(table_id) {
                                const table = this.tables.find(table => table.id === table_id);
                                axios.post('/api/admin/edit_table', { id: table_id, name: table.name, seats: table.seats }).then(response => {
                                    const data = response.data;
                                    if (!data.success) { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to edit table.', {error});
                                });
                            },

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
                                        const data = response.data;
                                        if (data.success)
                                        {
                                            this.user_search_result = data.users;
                                            if (this.user_search_result.length === 0) { this.user_not_found = true; }
                                            else { this.user_not_found = false; }
                                            this.user_show_results = true;
                                        }
                                        else { throw { response: {...response} }; }
                                    }).catch(error => {
                                        show_modal('Failed to search users.', {error});
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
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.user_data = data.user;
                                        this.user_reservations = data.reservations.map(reservation => ({
                                            ...reservation,
                                            start_full: new Date(`${reservation.start_date} ${reservation.start_time}`),
                                            end_full: new Date(`${reservation.end_date} ${reservation.end_time}`)
                                        }));
                                        this.sort_user_reservations();
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to get user data.', {error});
                                });
                            },
                            set_max_future_reservations(user_id, max_future_reservations) {
                                axios.post('/api/admin/set_max_future_reservations', { id: user_id, max_future_reservations: max_future_reservations }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.user_data.max_future_reservations = max_future_reservations;
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to set max future reservations.', {error});
                                });
                            },
                            set_max_future_reservations_with_input(user_id, max_future_reservations) {
                                show_modal('Enter the new reservation limit for the user.', {
                                    title: 'Change Reservation Limit',
                                    action: () => {
                                        max_future_reservations = Alpine.store('modal').input;
                                        if (!max_future_reservations) { max_future_reservations = null; }
                                        this.set_max_future_reservations(user_id, max_future_reservations);
                                    },
                                    confirm_text: 'Save',
                                    yes_class: 'bg-zinc-700 hover:bg-zinc-800',
                                    input_bool: true,
                                    input: max_future_reservations
                                });
                            },

                            //employees
                            employees: [],
                            employee_search: '',
                            employee_search_result: [],
                            employee_show_results: false,
                            employee_not_found: false,
                            search_employees() {
                                if (this.employee_search.length >= 2)
                                {
                                    axios.post('/api/admin/search_users', { search: this.employee_search }).then(response => {
                                        const data = response.data;
                                        if (data.success)
                                        {
                                            this.employee_search_result = data.users;
                                            if (this.employee_search_result.length === 0) { this.employee_not_found = true; }
                                            else { this.employee_not_found = false; }
                                            this.employee_show_results = true;
                                        }
                                        else { throw { response: {...response} }; }
                                    }).catch(error => {
                                        show_modal('Failed to search employees.', {error});
                                    });
                                } else {
                                    this.employee_search_result = [];
                                    this.employee_show_results = false;
                                }
                            },
                            add_employee(employee_id) {
                                axios.post('/api/admin/add_employee', { id: employee_id }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.employees.push(data.employee);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to add employee.', {error});
                                }).finally(() => {
                                    this.clear_employees();
                                });
                            },
                            remove_employee(employee_id) {
                                axios.post('/api/admin/remove_employee', { id: employee_id }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.employees = this.employees.filter(employee => employee.id !== employee_id);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to remove employee.', {error});
                                });
                            },
                            clear_employees() {
                                this.employee_search = '';
                                this.employee_show_results = false;
                            },

                            //reservations
                            get today() {
                                let date = new Date().toLocaleString(undefined, {timeZone: this.timezone});
                                date = new Date(date);
                                return style_html_date(date); //yyyy-mm-dd
                            },
                            date: null,
                            reservations: [],
                            reservations_loading: false,
                            reservations_loaded: false,
                            reservations_error: false,
                            tables: [],
                            get sorted_reservations() {
                                let sorted = this.reservations.map(reservation => {
                                    const now = new Date().toLocaleString(undefined, {timeZone: this.timezone});
                                    let status = 'upcoming';

                                    //check if reservation is on the next day
                                    let start = new Date(reservation.start_full);
                                    let end = new Date(reservation.end_full);

                                    const custom_hours = this.custom_opening_hours[style_html_date(start)];
                                    const day = start.getDay();
                                    const { open } = custom_hours || this.opening_hours[day];

                                    const opening_hour = parseInt(open);
                                    const opening_minutes = open.includes(':') ? parseInt(open.split(':')[1]) : 0;                                    
                                    if (parseInt(reservation.start_time.split(':')[0]) < opening_hour || (parseInt(reservation.start_time.split(':')[0]) === opening_hour && parseInt(reservation.start_time.split(':')[1]) < opening_minutes))
                                    {
                                        start = new Date(start.setDate(start.getDate() + 1));
                                        end = new Date(end.setDate(end.getDate() + 1));
                                    }

                                    if (start <= now && end > now) { status = 'ongoing'; }
                                    if (end <= now) { status = 'past'; }
                                    return { ...reservation, status };
                                }).sort((a, b) => {
                                    //first sort by status into groups
                                    const status = {upcoming: 0, ongoing: 1, past: 2};
                                    if (status[a.status] !== status[b.status]) { return status[a.status] - status[b.status]; }
                                }).sort((a, b) => {
                                    //then sort each group by start time and if equal by end time
                                    if (a.status === b.status)
                                    {
                                        let dateA = new Date(a.start_full);
                                        let dateB = new Date(b.start_full);
                                        let endA = new Date(a.end_full);
                                        let endB = new Date(b.end_full);

                                        //if opening hours are 16:00-02:00 and reservation is at 01:00. it should be on the next date
                                        //but we use still the previous date so it is tied to the previous date opening hours
                                        //so we need to check if the reservation should be on the next date and if so, we use the next date
                                        //find out by getting the opening hours
                                        const custom_hours = this.custom_opening_hours[style_html_date(dateA)];
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
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.reservations = data.reservations.map(reservation => ({
                                            ...reservation,
                                            start_full: new Date(`${reservation.start_date} ${reservation.start_time}`),
                                        end_full: new Date(`${reservation.end_date} ${reservation.end_time}`)
                                        }));
                                        this.tables = data.tables;
                                        this.reservations_loaded = true;
                                    }
                                    else { this.reservations_error = true; throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to get reservations.', {error});
                                    this.reservations_error = true;
                                }).finally(() => {
                                    this.reservations_loading = false;
                                });
                            },
                            cancel_reservation(reservation_id) {
                                show_modal('This action cannot be undone.', {
                                    title: 'Are you sure?',
                                    action: () => {
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
                                            else { throw { response: {...response} }; }
                                        }).catch(error => {
                                            show_modal('Failed to cancel reservation.', {error});
                                        });
                                    },
                                    confirm_text: 'Confirm'
                                });
                            },

                            //notes
                            edit_reservation_note(reservation_id, note) {
                                show_modal('Enter the new note for this reservation.', {
                                    title: 'Edit Note',
                                    action: () => {
                                        note = Alpine.store('modal').input;
                                        if (!note) { note = null; }
                                        axios.post('/api/admin/reservation_note', { id: reservation_id, note: note }).then(response => {
                                            const data = response.data;
                                            if (data.success)
                                            {
                                                //update note in reservations
                                                const reservation = this.reservations.find(r => r.id === reservation_id);
                                                if (reservation) { reservation.note = note; }

                                                //update note in user_reservations 
                                                const user_reservation = this.user_reservations.find(r => r.id === reservation_id);
                                                if (user_reservation) { user_reservation.note = note; }
                                            }
                                            else { throw { response: {...response} }; }
                                        }).catch(error => {
                                            show_modal('Failed to edit note.', {error});
                                        });
                                    },
                                    confirm_text: 'Save',
                                    yes_class: 'bg-zinc-700 hover:bg-zinc-800',
                                    input_bool: true,
                                    input: note
                                });
                            },
                            edit_user_note(user_id, note) {
                                show_modal('Enter the new note for the user.', {
                                    title: 'Edit Note',
                                    action: () => {
                                        note = Alpine.store('modal').input;
                                        if (!note) { note = null; }
                                        axios.post('/api/admin/user_note', { id: user_id, note: note }).then(response => {
                                            const data = response.data;
                                            if (data.success)
                                            {
                                                //update note in user
                                                const user = this.user_data;
                                                if (user) { user.note = note; }
                                            }
                                            else { throw { response: {...response} }; }
                                        }).catch(error => {
                                            show_modal('Failed to edit note.', {error});
                                        });
                                    },
                                    confirm_text: 'Save',
                                    yes_class: 'bg-zinc-700 hover:bg-zinc-800',
                                    input_bool: true,
                                    input: note
                                });
                            },

                            //durations
                            new_duration: null,
                            create_duration(duration) {
                                axios.post('/api/admin/create_duration', { duration: duration }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.durations.push(duration);
                                        this.durations.sort((a, b) => a - b);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to create duration.', {error});
                                }).finally(() => {
                                    this.new_duration = null;
                                });
                            },
                            delete_duration(duration) {
                                axios.post('/api/admin/delete_duration', { duration: duration }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.durations = this.durations.filter(d => d !== duration);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to delete duration.', {error});
                                });
                            },

                            //opening hours
                            toggle_day_status(day_index, day_name) {
                                if (this.closing_dates.includes(day_name))
                                {
                                    this.closing_dates = this.closing_dates.filter(d => d !== day_name);
                                }
                                else
                                {
                                    this.closing_dates.push(day_name);
                                }
                            },
                            save_opening_hours(day_index, day_name) {
                                const hours = this.opening_hours[day_index];
                                if (!hours && !this.closing_dates.includes(day_name))
                                {
                                    show_modal('Please set both opening and closing hours.');
                                    return;
                                }
                                axios.post('/api/admin/save_opening_hours', {
                                    day: day_name,
                                    open: hours?.open || null,
                                    close: hours?.close || null,
                                    closed: this.closing_dates.includes(day_name)
                                }).then(response => {
                                    const data = response.data;
                                    if (!data.success) { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to save opening hours.', {error});
                                });
                            },

                            //special hours
                            new_special_date: '',
                            new_special_open: '',
                            new_special_close: '',
                            save_special_hours(closed = false) {
                                if (!this.new_special_date || (!closed && (!this.new_special_open || !this.new_special_close)))
                                {
                                    show_modal('Please fill in all the required fields.');
                                    return;
                                }
                                this.new_special_open = style_time(new Date(`${this.new_special_date} ${this.new_special_open}`));
                                this.new_special_close = style_time(new Date(`${this.new_special_date} ${this.new_special_close}`));
                                axios.post('/api/admin/save_special_hours', {
                                    date: this.new_special_date,
                                    open: this.new_special_open,
                                    close: this.new_special_close,
                                    closed: closed
                                }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        if (closed)
                                        {
                                            //remove from custom opening hours
                                            delete this.custom_opening_hours[this.new_special_date];
                                            //add to closing dates
                                            this.closing_dates.push(this.new_special_date);
                                        }
                                        else
                                        {
                                            //add to custom opening hours
                                            this.custom_opening_hours[this.new_special_date] = { open: this.new_special_open, close: this.new_special_close, close_on_next_day: new Date(`${this.new_special_date} ${this.new_special_close}`) < new Date(`${this.new_special_date} ${this.new_special_open}`) };
                                            //remove from closing dates
                                            this.closing_dates = this.closing_dates.filter(d => d !== this.new_special_date);
                                        }

                                        //reset form
                                        this.new_special_date = '';
                                        this.new_special_open = '';
                                        this.new_special_close = '';
                                    } else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to save special hours.', {error});
                                });
                            },

                            delete_special_hours(date) {
                                axios.post('/api/admin/delete_special_hours', { date: date }).then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        delete this.custom_opening_hours[date];
                                        this.closing_dates = this.closing_dates.filter(d => d !== date);
                                    }
                                    else { throw { response: {...response} }; }
                                }).catch(error => {
                                    show_modal('Failed to delete special hours.', {error});
                                });
                            },

                            //helpers
                            sort_user_reservations() {
                                //sort user reservations by date and time
                                this.user_reservations.sort((a, b) => {
                                    const dateA = new Date(a.start_full);
                                    const dateB = new Date(b.start_full);
                                    if (dateA - dateB === 0)
                                    {
                                        const endA = new Date(a.end_full);
                                        const endB = new Date(b.end_full);
                                        return endA - endB;
                                    }
                                    return dateA - dateB;
                                });
                            },
                            get_future_reservations(reservations) {
                                return reservations.filter(r => new Date(r.start_full) >= new Date(new Date().toLocaleString(undefined, {timeZone: this.timezone})));
                            },
                            get_past_reservations(reservations) {
                                return reservations.filter(r => new Date(r.start_full) < new Date(new Date().toLocaleString(undefined, {timeZone: this.timezone})));
                            },
                            get_sorted_special_dates() {
                                const special_dates = Object.keys(this.custom_opening_hours);
                                const closing_dates = this.closing_dates.filter(d => d.match(/^\d{4}-\d{2}-\d{2}$/));
                                return [...special_dates, ...closing_dates].sort((a,b) => new Date(a) - new Date(b));
                            },
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</body>

</html>