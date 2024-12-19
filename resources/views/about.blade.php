<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-auto">

<head>
    <title>{{ config('app.name') }} - Information</title>
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
                <div x-init="get_info_data()" x-data="info_data()" class="max-w-4xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-y-8">

                    <!-- restaurant location -->
                    <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-4">
                            <h2 class="text-2xl font-bold tracking-wide">Location</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </div>
                        <div class="aspect-video w-full rounded-lg overflow-hidden">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d798.8889872194158!2d16.62070427610695!3d49.18039747025195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x471295033106c993%3A0xc31cfdec2798b358!2sSvatopetrsk%C3%A1%2035%2F7%2C%20617%2000%20Brno-jih!5e1!3m2!1sen!2scz!4v1734460033374!5m2!1sen!2scz"
                                class="w-full h-full"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                    <!-- opening hours -->
                    <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-4">
                            <h2 class="text-2xl font-bold tracking-wide">Opening Hours</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Regular Hours</h3>
                                <template x-for="(hours, day) in opening_hours" :key="day">
                                    <div class="flex justify-between py-1">
                                        <span x-text="['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][day]"></span>
                                        <span x-text="closing_dates.includes(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][day]) ? 'Closed' : hours.open + ' - ' + hours.close"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Special Hours</h3>
                                <template x-if="Object.keys(custom_opening_hours).length === 0 && !closing_dates.some(date => date.match(/^\d{4}-\d{2}-\d{2}$/))">
                                    <p class="text-zinc-600">No special hours currently scheduled.</p>
                                </template>
                                <template x-for="date in [...Object.keys(custom_opening_hours), ...closing_dates.filter(d => d.match(/^\d{4}-\d{2}-\d{2}$/))].sort((a,b) => new Date(a) - new Date(b))" :key="date">
                                    <div class="flex justify-between py-1">
                                        <span x-text="new Date(date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})"></span>
                                        <span x-text="date in custom_opening_hours ? custom_opening_hours[date].open + ' - ' + custom_opening_hours[date].close : 'Closed'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- faq -->
                    <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-4">
                            <h2 class="text-2xl font-bold tracking-wide">FAQ</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div class="flex flex-col gap-y-2">
                            <template x-for="(question, index) in faq" :key="index">
                                <div x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded" type="button" class="w-full py-2 text-left flex justify-between items-center">
                                        <span class="font-medium" x-text="question.q"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" :class="{ 'rotate-180': expanded }">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    <p x-show="expanded" x-collapse class="pb-2 text-zinc-600" x-text="question.a"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- contact information -->
                    <div class="bg-white overflow-hidden shadow-md rounded-lg px-6 py-8">
                        <div class="flex items-center gap-2 mb-4">
                            <h2 class="text-2xl font-bold tracking-wide">Contact Information</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mb-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                        </div>
                        <div class="flex flex-col gap-y-2">
                            <p class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                                <span>Phone: <a :href="'tel:' + phone.replace(/\s+/g, '')" x-text="phone" class="text-zinc-700 hover:text-zinc-900"></a></span>
                            </p>
                            <p class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />
                                </svg>
                                <span>Email: <a :href="'mailto:' + email" x-text="email" class="text-zinc-700 hover:text-zinc-900"></a></span>
                            </p>
                        </div>
                    </div>

                    @include('modal')
                </div>

                <script>
                    function info_data() {
                        return {
                            //server configuration
                            opening_hours: {},
                            custom_opening_hours: {},
                            closing_dates: [],
                            phone: '',
                            email: '',
                            get_info_data() {
                                axios.post('/api/info_data').then(response => {
                                    const data = response.data;
                                    if (data.success)
                                    {
                                        this.opening_hours = data.opening_hours;
                                        this.custom_opening_hours = data.custom_opening_hours;
                                        this.closing_dates = data.closing_dates;
                                        this.phone = data.phone;
                                        this.email = data.email;
                                    }
                                });
                            },

                            faq: [
                                {q: 'Do I need to login in order to make a reservation?', a: 'Yes, this is so you can manage your reservations.'},
                                {q: 'Can I cancel my reservation?', a: 'Yes, you can cancel your reservation at any time without any charges.'},
                                {q: 'There are no available tables for my desired date and time, what can I do?', a: 'You may give us a call and we will try to arrange a table for you.'},
                                {q: 'I need to reserve multiple tables, is that possible?', a: 'Yes, we can arrange a reservation for a larger group of people over the phone.'},
                                {q: 'Is there a parking available?', a: 'Yes, there is a small parking lot in front of the restaurant.'}
                            ]
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</body>

</html>