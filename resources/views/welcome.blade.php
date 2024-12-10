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

<body class="bg-stone-900 text-stone-300">
    <div class="main-container font-sans">

        @include('navbar')

        <div class="min-h-screen px-3 leading-normal tracking-normal">

            <div class="container py-8 mx-auto">
                <div class="flex flex-col justify-center items-center">
                    <h1 class="text-3xl font-bold">Reservations</h1>
                    <p class="text-lg">Currently available tables: 10</p>
                </div>
            </div>

        </div>

        <?php if (app()->environment('local')) { echo "Laravel v" . Illuminate\Foundation\Application::VERSION . " | PHP v" . PHP_VERSION; } ?>

    </div>
</body>

</html>