<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'NetDevice Manager') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900 selection:bg-indigo-500/30">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="hidden md:flex md:flex-shrink-0 w-64 flex-col bg-slate-900 shadow-xl z-20">
                <div class="flex items-center h-20 px-6 bg-slate-900 border-b border-white/5">
                    <div class="relative group cursor-pointer">
                        <div class="absolute inset-0 bg-indigo-500 rounded-lg blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <div class="relative p-2 bg-slate-800 rounded-xl border border-white/10 shadow-lg">
                            <x-application-logo class="w-8 h-8" />
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="font-black text-xs tracking-widest uppercase text-white leading-tight">NetDevice</div>
                        <div class="text-[9px] text-indigo-400 font-bold uppercase tracking-widest opacity-80">PRO MANAGER</div>
                    </div>
                </div>
                
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    <x-nav-link-sidebar :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">Tableau de Bord</x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('devices.index')" :active="request()->routeIs('devices.*')" icon="devices">Appareils</x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('error-logs.index')" :active="request()->routeIs('error-logs.*')" icon="logs">Erreurs</x-nav-link-sidebar>
                    
                    <div class="pt-4 pb-2 text-xs font-semibold text-slate-500 uppercase">Analyses</div>
                    <x-nav-link-sidebar :href="route('statistics.global')" :active="request()->routeIs('statistics.global')" icon="analytics">Analyses Globales</x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('statistics.toner')" :active="request()->routeIs('statistics.toner')" icon="toner">Niveaux Toner</x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="reports">Rapports PDF</x-nav-link-sidebar>
                    
                    @role('admin')
                    <div class="pt-4 pb-2 text-xs font-semibold text-slate-500 uppercase">Paramètres</div>
                    <x-nav-link-sidebar :href="route('alerts.recipients')" :active="request()->routeIs('alerts.recipients')" icon="mail">Destinataires Alertes</x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('settings.diagnostics')" :active="request()->routeIs('settings.diagnostics')" icon="logs">Diagnostics</x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('admin.users')" :active="request()->routeIs('admin.users')" icon="users">Gestion Utilisateurs</x-nav-link-sidebar>
                    @endrole
                </nav>

                <div class="p-3 border-t border-white/5 bg-slate-900/50 text-[10px]">
                    <div class="flex items-center gap-2 p-1.5 rounded-lg bg-white/5">
                        <div class="w-6 h-6 rounded-md bg-indigo-500 flex items-center justify-center text-white font-bold text-[10px]">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="truncate">
                            <div class="text-[11px] font-bold text-white truncate">{{ Auth::user()->name }}</div>
                            <div class="text-[9px] text-slate-500 uppercase tracking-tighter">{{ Auth::user()->roles->first()->name ?? 'Utilisateur' }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-900 text-[10px] text-slate-600 border-t border-white/5">
                    &copy; 2026 NetDevice Manager
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-10">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-slate-800">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @livewireScripts
        @stack('scripts')
    </body>
</html>

