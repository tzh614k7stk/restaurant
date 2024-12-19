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
                <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-y-8">

                    <!-- reservations -->
                    <div x-data="admin_data" x-init="get_reservations" class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-6">
                            <h2 class="text-2xl font-bold tracking-wide">Reservations</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>

                        <div class="flex flex-col gap-y-4">
                            <!-- date selector -->
                            <div class="flex flex-row gap-2">
                                <input x-model="date" :min="new Date().toISOString().split('T')[0]" x-init="date = new Date().toISOString().split('T')[0]" type="date" class="bg-white shadow appearance-none border rounded py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                <button @click="get_reservations" class="flex relative items-center gap-2 bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Update
                                </button>
                            </div>
                            <!-- reservations for selected date -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Table</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-zinc-200">
                                        <template x-for="reservation in reservations" :key="reservation.id">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.time"></td>
                                                <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.name"></td>
                                                <td class="px-6 py-4 whitespace-nowrap" x-text="reservation.table"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('modal')
                </div>

                <script>
                    function admin_data() {
                        return {
                            //reservations
                            date: null,
                            reservations: [],
                            get_reservations() {
                                axios.post('/api/admin/reservations', { date: this.date }).then(response => {
                                    this.reservations = response.data;
                                });
                            }
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</body>

</html>