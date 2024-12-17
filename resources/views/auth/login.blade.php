<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-auto">

<head>
    <title>{{ config('app.name') }} - Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <noscript>
        Please enable javascript in order to continue.
        <style>.main-container { display: none; }</style>
    </noscript>

    @vite('resources/css/tailwind.css')
    @vite('resources/css/app.css')

    @livewireStyles
    @livewireScripts
</head>

<body class="bg-gradient-to-b from-zinc-50 to-zinc-300 text-zinc-900">
    <div class="main-container">
        @include('navbar')

        <div class="min-h-screen px-3 leading-relaxed">
            <div class="container py-16 mx-auto">
                <div class="max-w-md mx-auto">
                    <div class="bg-white overflow-hidden shadow-md rounded-lg">
                        <div class="px-6 py-8">                    
                            <!-- title -->
                            <div class="flex items-center gap-2 mb-6">
                                <h2 class="text-2xl font-bold tracking-wide">Login</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                            </div>

                            <!-- form -->
                            <form method="POST" action="{{ route('login') }}">
                                @csrf                                
                                <!-- email -->
                                <div class="mb-6">
                                    <label for="email" class="block text-zinc-700 text-sm font-bold mb-1">Email</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email') }}" 
                                           required 
                                           autofocus
                                           class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none @error('email') border-rose-500 @enderror">
                                    @error('email')
                                        <span class="text-rose-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- password -->
                                <div class="mb-6">
                                    <label for="password" class="block text-zinc-700 text-sm font-bold mb-1">Password</label>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           required
                                           class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none @error('password') border-rose-500 @enderror">
                                    @error('password')
                                        <span class="text-rose-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- submit -->
                                <button type="submit" class="w-full bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                    Login
                                </button>
                                <!-- register page link -->
                                <div class="mt-4 text-center">
                                    <span class="text-zinc-600">Don't have an account?</span>
                                    <a href="{{ route('register') }}" class="text-zinc-700 hover:text-zinc-800 font-semibold">Register</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>