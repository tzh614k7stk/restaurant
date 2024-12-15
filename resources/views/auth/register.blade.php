<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-auto">

<head>
    <title>{{ config('app.name') }} - Register</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <noscript>
        Please enable javascript in order to continue.
        <style>.main-container { display: none; }</style>
    </noscript>

    @vite('resources/css/tailwind.css')
    @vite('resources/css/app.css')
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
                                <h2 class="text-2xl font-bold tracking-wide">Register</h2>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                            </div>

                            <!-- form -->
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <!-- name -->
                                <div class="mb-6">
                                    <label for="name" class="block text-zinc-700 text-sm font-bold mb-1">Name</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}" 
                                           required 
                                           autofocus
                                           class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none @error('name') border-rose-500 @enderror">
                                    @error('name')
                                        <span class="text-rose-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- email -->
                                <div class="mb-6">
                                    <label for="email" class="block text-zinc-700 text-sm font-bold mb-1">Email</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email') }}" 
                                           required
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
                                <!-- confirm password -->
                                <div class="mb-6">
                                    <label for="password_confirmation" class="block text-zinc-700 text-sm font-bold mb-1">Confirm Password</label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation" 
                                           required
                                           class="bg-white shadow appearance-none border rounded w-full py-2 px-3 text-zinc-700 leading-tight focus:outline-none">
                                </div>
                                <!-- submit -->
                                <button type="submit" class="w-full bg-zinc-700 hover:bg-zinc-800 active:bg-zinc-950 text-white font-bold rounded py-2 px-3 focus:outline-none">
                                    Register
                                </button>
                                <!-- login page link -->
                                <div class="mt-4 text-center">
                                    <span class="text-zinc-600">Already have an account?</span>
                                    <a href="{{ route('login') }}" class="text-zinc-700 hover:text-zinc-800 font-semibold">Login</a>
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