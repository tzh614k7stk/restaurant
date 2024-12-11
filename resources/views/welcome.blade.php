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

    @livewireStyles
    @livewireScripts

    @vite('resources/css/app.css')
</head>

<body class="bg-gradient-to-b from-zinc-50 to-zinc-300 text-zinc-900">
    <div class="main-container">

        @include('navbar')

        <div class="min-h-screen px-3 leading-relaxed tracking-normal">

            <div class="container py-8 mx-auto">
                <div class="flex justify-center items-center">
                    <div class="flex flex-col justify-center items-center bg-white p-8 border-b border-zinc-200">
                        <h1 class="text-3xl font-bold tracking-wider">Reservations</h1>
                        <p class="text-lg mb-6">Currently available tables: 10</p>
                        <div class="flex gap-4">
                            <button class="text-sm font-medium transition-transform hover:scale-105 px-3 py-2">Login</button>
                            <button class="text-sm font-medium transition-transform hover:scale-105 px-3 py-2">Sign up</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php if (app()->environment('local')) { echo "Laravel v" . Illuminate\Foundation\Application::VERSION . " | PHP v" . PHP_VERSION; } ?>

    </div>
</body>

</html>