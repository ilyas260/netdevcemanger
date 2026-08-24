<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center bg-no-repeat relative" style="background-image: url('{{ asset('images/network_bg.png') }}');">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <div class="mb-2">
                    <a href="/" class="flex flex-col items-center group">
                        <!-- Icon Structure -->
                        <div class="relative p-1">
                            <div class="absolute inset-0 bg-indigo-500 rounded-[2.5rem] blur-xl opacity-20 group-hover:opacity-40 transition-opacity duration-700"></div>
                            <div class="relative p-4 sm:p-8 bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl rounded-[2rem] sm:rounded-[3rem] group-hover:scale-105 transition-all duration-500 ease-out">
                                <x-application-logo class="w-20 h-20 sm:w-32 sm:h-32" />
                            </div>
                        </div>
                        
                        <!-- Title Structure -->
                        <div class="mt-4 sm:mt-6 text-center">
                            <h1 class="text-xl sm:text-3xl font-black text-white tracking-[0.2em] uppercase leading-none">
                                NetDevice
                            </h1>
                            <div class="flex items-center justify-center gap-2 mt-1 sm:mt-2">
                                <div class="h-px w-4 sm:w-8 bg-indigo-400/50"></div>
                                <span class="text-indigo-400 font-bold tracking-[0.3em] uppercase text-[10px] sm:text-sm">Manager</span>
                                <div class="h-px w-4 sm:w-8 bg-indigo-400/50"></div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="w-full sm:max-w-md mt-8 px-8 py-10 bg-white/95 backdrop-blur shadow-2xl overflow-hidden sm:rounded-3xl border border-white/20">
                    {{ $slot }}
                </div>

                <!-- Stats Footer at Login -->
                <div class="mt-8 grid grid-cols-3 gap-8 text-white/80 text-center animate-pulse">
                    <div>
                        <div class="text-xl font-bold">100%</div>
                        <div class="text-[10px] uppercase tracking-widest text-white/50">Disponibilité</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold">{{ \App\Models\Device::count() }}</div>
                        <div class="text-[10px] uppercase tracking-widest text-white/50">Appareils</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold">24/7</div>
                        <div class="text-[10px] uppercase tracking-widest text-white/50">Surveillance</div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
