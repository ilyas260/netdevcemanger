<div class="space-y-4" @if($isCheckingAll) wire:poll.3s="checkScanProgress" @endif>
    @if($isCheckingAll)
        <div class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg flex justify-between items-center animate-pulse">
            <div class="flex items-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-white" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-bold uppercase tracking-wider">Actualisation en cours... Les icônes vont passer au vert au fur et à mesure.</span>
            </div>
            <button wire:click="stopChecking" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded text-xs font-bold transition">Arrêter</button>
        </div>
    @endif
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center bg-white p-4 rounded-lg shadow-sm border">
        <div class="flex flex-1 w-full gap-2 items-center">
            <div class="relative flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par nom ou IP..." 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            @unlessrole('consultant')
            <button wire:click="pingAllDevices" wire:loading.attr="disabled" 
                    class="flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                <svg wire:loading.remove wire:target="pingAllDevices" class="h-4 w-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <svg wire:loading wire:target="pingAllDevices" class="animate-spin h-4 w-4 mr-2 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="hidden sm:inline">Actualiser les statuts</span>
            </button>
            @endunlessrole
        </div>
        
        <div class="flex gap-2 w-full md:w-auto">
            <select wire:model.live="selectedAgencyId" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Toutes les agences</option>
                @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les types</option>
                <option value="pc">PC</option>
                <option value="imprimante">Imprimante</option>
                <option value="switch">Switch</option>
                <option value="serveur">Serveur</option>
                <option value="autre">Autre</option>
            </select>

            <select wire:model.live="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-3 bg-green-100 border-l-4 border-green-500 text-green-700 text-sm rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg border">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appareil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider flex items-center">
                        Statut
                        <svg class="w-3 h-3 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière vue</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($devices as $device)
                    <tr class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('devices.show', $device) }}" class="group">
                                <div class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition">{{ $device->name }}</div>
                                <div class="text-xs text-slate-500">{{ $device->brand }} {{ $device->model }}</div>
                                @if($device->agency)
                                <div class="text-xs text-indigo-600 mt-1 flex items-center gap-1 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $device->agency->name }}
                                </div>
                                @endif
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <code>{{ $device->ip_address }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg group-hover:scale-110 group-hover:bg-indigo-100 group-hover:animate-float-fast transition-all duration-300">
                                    <x-device-icon :type="$device->type" class="w-4 h-4" />
                                </div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-700 uppercase group-hover:bg-indigo-50 group-hover:text-indigo-700 transition-colors">
                                    {{ $device->type }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <!-- Statut Réseau -->
                                @php
                                    $status = $device->status ?: 'offline';
                                    $statusColor = match($status) {
                                        'online' => 'bg-green-500',
                                        'offline' => 'bg-red-500',
                                        'unstable' => 'bg-orange-500',
                                        'slow' => 'bg-yellow-500',
                                        default => 'bg-gray-500'
                                    };
                                    $statusText = match($status) {
                                        'online' => 'En ligne',
                                        'offline' => 'Injoignable',
                                        'unstable' => 'Instable',
                                        'slow' => 'Lent',
                                        default => 'Inconnu'
                                    };
                                    $textColor = match($status) {
                                        'online' => 'text-green-700',
                                        'offline' => 'text-red-700',
                                        'unstable' => 'text-orange-700',
                                        'slow' => 'text-yellow-700',
                                        default => 'text-gray-700'
                                    };
                                @endphp
                                <div class="flex items-center">
                                    <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full {{ $statusColor }} mr-2"></span>
                                    <span class="text-xs font-bold {{ $textColor }} uppercase">
                                        {{ $statusText }}
                                    </span>
                                </div>
                                
                                <!-- Statut Supervision (Toggle) -->
                                @unlessrole('consultant')
                                <button wire:click="toggleStatus({{ $device->id }})" 
                                        class="text-[10px] text-left hover:underline {{ $device->is_active ? 'text-indigo-600' : 'text-slate-400' }}">
                                    Surveillance : {{ $device->is_active ? 'Activée' : 'Désactivée' }}
                                </button>
                                @else
                                <span class="text-[10px] {{ $device->is_active ? 'text-indigo-600' : 'text-slate-400' }}">
                                    Surveillance : {{ $device->is_active ? 'Activée' : 'Désactivée' }}
                                </span>
                                @endunlessrole
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $device->updated_at ? $device->updated_at->diffForHumans() : 'Jamais' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @unlessrole('consultant')
                            <a href="{{ route('devices.edit', $device) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition-colors duration-200">Éditer</a>
                            @endunlessrole
                            <a href="{{ route('devices.ping-history', $device) }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 px-2 py-1 rounded transition-colors duration-200">Historique</a>
                            @unlessrole('consultant')
                            <form action="{{ route('devices.ping', $device) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded transition-colors duration-200">Ping</button>
                            </form>
                            <button wire:click="deleteDevice({{ $device->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cet appareil ? L'historique sera conservé."
                                    class="inline-flex items-center text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition-colors duration-200">
                                Supprimer
                            </button>
                            @endunlessrole
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 font-medium">
                            Aucun appareil trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 bg-gray-50">
            {{ $devices->links() }}
        </div>
    </div>
</div>
