    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            {{ __('Découverte Réseau SNMP Intelligent') }}
        </h2>
    </x-slot>

    <div class="py-8" @if($isScanning) wire:poll.2s="checkScanStatus" @endif>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session()->has('message'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                    <strong class="font-bold">Succès!</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                    <strong class="font-bold">Erreur!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Configuration du scan -->
                        <div class="col-span-1 space-y-4 border-r border-gray-100 pr-0 md:pr-6">
                            <h3 class="text-lg font-bold text-gray-800">Configuration</h3>
                            <p class="text-xs text-gray-500 mb-4">Utilisez le scan SNMP pour identifier automatiquement les équipements sur le réseau.</p>
                            
                            <div>
                                <label for="ipRange" class="block text-sm font-medium text-gray-700 flex justify-between">
                                    <span>Plage d'adresses IP</span>
                                    <span class="text-[10px] text-blue-600 font-semibold uppercase tracking-wider">Automatisé</span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" id="ipRange" wire:model="ipRange" 
                                           class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md sm:text-sm transition-all duration-200" 
                                           placeholder="ex: 192.168.1.0/24">
                                </div>
                                @error('ipRange') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="agency" class="block text-sm font-medium text-gray-700">Agence de destination</label>
                                <select id="agency" wire:model.live="selectedAgencyId" wire:change="updateIpRangeFromAgency"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="">-- Sélectionnez une agence --</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency['id'] }}" wire:key="agency-{{ $agency['id'] }}">{{ $agency['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('selectedAgencyId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4 space-y-3">
                                @unlessrole('consultant')
                                <button wire:click="scanNetwork" wire:loading.attr="disabled" {{ !$selectedAgencyId ? 'disabled' : '' }}
                                        class="w-full flex justify-center items-center px-4 py-3 {{ !$selectedAgencyId ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700' }} border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] disabled:opacity-50">
                                    <svg wire:loading wire:target="scanNetwork" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="scanNetwork">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </span>
                                    <span wire:loading.remove wire:target="scanNetwork">Démarrer le Scan SNMP</span>
                                    <span wire:loading wire:target="scanNetwork">Scan en cours...</span>
                                </button>
                                @else
                                <div class="w-full flex justify-center items-center px-4 py-3 bg-gray-200 border border-transparent rounded-xl font-bold text-xs text-gray-500 uppercase tracking-widest cursor-not-allowed">
                                    Démarrer le Scan SNMP (Lecture Seule)
                                </div>
                                @endunlessrole

                                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded">
                                    <p class="text-[9px] text-blue-700 leading-tight">
                                        Le scan SNMP interroge les appareils en parallèle pour identifier les marques et modèles automatiquement.
                                    </p>
                                </div>
                            </div>                  </div>     </div>
                        </div>

                        <!-- Résultats du scan -->
                        <div class="col-span-1 md:col-span-2">
                            <div class="flex justify-between items-end mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Résultats de la découverte</h3>
                                    <p class="text-xs text-gray-500">
                                        @if(empty($scanResults) && !$isScanning)
                                            Aucun appareil découvert pour le moment.
                                        @else
                                            {{ count($scanResults) }} appareil(s) trouvé(s).
                                        @endif
                                    </p>
                                </div>
                                @if(!empty($scanResults))
                                <div>
                                    @unlessrole('consultant')
                                    <button wire:click="selectAllNew" class="text-xs text-indigo-600 font-medium hover:text-indigo-800 mr-4">Sélectionner les nouveaux</button>
                                    <button wire:click="addSelectedDevices" class="px-4 py-2 bg-green-600 text-white text-xs font-bold uppercase rounded hover:bg-green-700 transition">
                                        Ajouter ({{ count($selectedDevices) }})
                                    </button>
                                    @endunlessrole
                                </div>
                                @endif
                            </div>

                            @if($isScanning)
                                <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                                    <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
                                    <p class="text-indigo-600 font-bold uppercase tracking-wider text-sm">Scan en cours (Parallèle)...</p>
                                    <p class="text-xs text-gray-400 mt-2">Exploration de {{ $ipRange }}</p>
                                    
                                    <div class="w-full max-w-xs mt-6 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
                                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $scanProgress }}%"></div>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-2">{{ $scanProgress }}% complété</p>
                                </div>
                            @elseif(!empty($scanResults))
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10"></th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse IP</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôte / Constructeur</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse MAC</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($scanResults as $result)
                                                @php $isSelected = in_array($result['ip'], $selectedDevices); @endphp
                                                <tr class="{{ $result['exists'] ? 'bg-gray-50 opacity-75' : ($isSelected ? 'bg-blue-100 border-l-4 border-blue-500' : 'hover:bg-blue-50 cursor-pointer transition-colors') }}"
                                                    @if(!$result['exists']) wire:click="toggleDeviceSelection('{{ $result['ip'] }}')" @endif>
                                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                                        @if(!$result['exists'])
                                                            <div class="relative flex items-center justify-center">
                                                                <input type="checkbox" 
                                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-5 w-5" 
                                                                       value="{{ $result['ip'] }}" 
                                                                       wire:model.live="selectedDevices"
                                                                       onclick="event.stopPropagation()">
                                                                @if($isSelected)
                                                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-sm {{ $isSelected ? 'text-blue-800 font-bold' : ($result['exists'] ? 'text-gray-400' : 'text-gray-900 font-bold') }}">
                                                        {{ $result['ip'] }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        <div class="font-medium {{ $isSelected ? 'text-blue-900' : 'text-gray-800' }}">{{ $result['hostname'] }}</div>
                                                        <div class="text-xs">{{ $result['vendor'] ?: 'Appareil Actif' }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-gray-400 uppercase">
                                                        {{ $result['mac'] ?: 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                        @if($result['exists'])
                                                            <span class="px-2 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-gray-200 text-gray-700 uppercase tracking-tighter">
                                                                En Base
                                                            </span>
                                                        @elseif($isSelected)
                                                            <span class="px-2 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-blue-600 text-white uppercase tracking-tighter animate-pulse">
                                                                Sélectionné
                                                            </span>
                                                        @else
                                                            <span class="px-2 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-green-100 text-green-800 uppercase tracking-tighter">
                                                                Nouveau
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun scan récent</h3>
                                    <p class="mt-1 text-sm text-gray-500">Sélectionnez une agence et cliquez sur "Démarrer le Scan SNMP" pour lancer la découverte des équipements.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
