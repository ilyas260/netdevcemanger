<div class="p-6 bg-gray-50 min-h-screen" wire:poll.15s>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('agencies.index') }}" class="p-2 bg-white rounded-full shadow-sm hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Dashboard : {{ $agency->name }}</h1>
            </div>
            <div class="flex gap-2">
                @unlessrole('consultant')
                <a href="{{ route('devices.create', ['agency_id' => $agency->id]) }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition flex items-center shadow-sm font-semibold">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajouter Appareil
                </a>
                <button wire:click="pingRouter" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center shadow-md font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Ping Routeur
                </button>
                @endunlessrole
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        <!-- Global Agency Status Card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-4 bg-{{ $agency->status_color }}-100 rounded-xl">
                    <svg class="w-8 h-8 text-{{ $agency->status_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Routeur {{ $agency->hostname ? '('.$agency->hostname.')' : '' }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ __($agency->status) }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        IP: {{ $agency->router_ip }} 
                        @if($agency->debit_cible) | Débit: <span class="font-semibold text-gray-600">{{ $agency->debit_cible }}</span> @endif
                        @if($agency->nd_technique) | ND: <span class="font-semibold text-gray-600">{{ $agency->nd_technique }}</span> @endif
                    </p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-4 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Appareils</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $agency->devices->count() }}</p>
                    <div class="flex items-center space-x-2 mt-1.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200" title="Appareils Actifs">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse mr-1.5"></span>
                            {{ $agency->devices->whereIn('status', ['online', 'slow', 'unstable'])->count() }} Actifs
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200" title="Appareils Inactifs">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                            {{ $agency->devices->whereNotIn('status', ['online', 'slow', 'unstable'])->count() }} Inactifs
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-4 bg-purple-100 rounded-xl">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Dernière Vérification</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $agency->last_ping_at ? $agency->last_ping_at->format('H:i:s') : '--:--' }}</p>
                    <div class="flex items-center mt-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse mr-2"></span>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Synchronisé</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices List -->
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Équipements de l'agence
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($devices as $device)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition duration-200 overflow-hidden group">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ $device->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono">{{ $device->ip_address }}</p>
                            </div>
                             <div class="flex items-center space-x-2">
                                @php
                                    $statusColor = match($device->status) {
                                        'online' => 'bg-green-500 animate-pulse',
                                        'slow' => 'bg-yellow-500',
                                        'unstable' => 'bg-orange-500',
                                        'offline' => 'bg-red-500',
                                        default => 'bg-gray-400'
                                    };
                                    $statusLabel = match($device->status) {
                                        'online' => 'En ligne',
                                        'slow' => 'Lent',
                                        'unstable' => 'Instable',
                                        'offline' => 'Hors-ligne',
                                        default => 'Inconnu'
                                    };
                                @endphp
                                <span class="w-3 h-3 rounded-full {{ $statusColor }}" title="Statut : {{ $statusLabel }}"></span>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $device->type }}</span>
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Modèle:</span>
                                <span class="text-gray-800 font-medium">{{ $device->model ?: 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Dernier passage:</span>
                                <span class="text-gray-800">{{ $device->last_seen_at ? $device->last_seen_at->format('d/m H:i') : 'Inconnu' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Dernière vérif:</span>
                                <span class="text-gray-800 font-mono text-xs text-indigo-600 font-bold">{{ $device->updated_at ? $device->updated_at->format('H:i:s') : 'Jamais' }}</span>
                            </div>
                        </div>

                        @unlessrole('consultant')
                        <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                            <button wire:click="pingDevice({{ $device->id }})" 
                                    class="flex-1 py-2 bg-gray-50 text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition flex justify-center items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Tester
                            </button>
                            <button wire:click="deleteDevice({{ $device->id }})" 
                                    wire:confirm="Retirer cet appareil de l'agence ?"
                                    class="ml-2 p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                    title="Supprimer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        @endunlessrole
                    </div>
                </div>
            @endforeach
        </div>

        @if($devices->isEmpty())
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="text-gray-500 text-lg">Aucun appareil assigné à cette agence.</p>
                <p class="text-gray-400 text-sm">Allez dans la gestion des appareils pour en ajouter.</p>
            </div>
        @endif
    </div>
</div>
