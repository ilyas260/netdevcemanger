<div class="bg-white p-6 rounded-lg shadow-sm border space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Test de connectivité - {{ $device->name }}</h3>
        <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $device->ip_address }}</span>
    </div>

    @if (session()->has('info'))
        <div class="p-3 mb-4 text-sm text-blue-700 bg-blue-100 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex gap-4 items-end">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de paquets</label>
            <input wire:model="duration_sec" type="number" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   {{ $is_running ? 'disabled' : '' }}>
        </div>
        <button wire:click="launchPing" 
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:bg-indigo-300 transition"
                {{ $is_running ? 'disabled' : '' }}>
            <span wire:loading.remove wire:target="launchPing">Lancer le ping</span>
            <span wire:loading wire:target="launchPing">Préparation...</span>
        </button>
    </div>

    @if($is_running)
        <div wire:poll.1s="checkResult" class="flex items-center gap-3 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
            <svg class="animate-spin h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm text-yellow-800 font-medium">Ping en cours d'exécution...</span>
        </div>
    @endif

    @if($lastResult)
        <div class="mt-4 p-4 rounded-lg border {{ $lastResult->status === 'online' ? 'bg-green-50 border-green-200' : ($lastResult->status === 'offline' ? 'bg-red-50 border-red-200' : 'bg-orange-50 border-orange-200') }}">
            <div class="flex items-center justify-between mb-3">
                <span class="uppercase font-bold text-sm tracking-widest {{ $lastResult->status === 'online' ? 'text-green-800' : ($lastResult->status === 'offline' ? 'text-red-800' : 'text-orange-800') }}">
                    Statut : {{ $lastResult->status }}
                </span>
                <span class="text-xs text-gray-500">{{ $lastResult->tested_at->format('H:i:s') }}</span>
            </div>
            
            <p class="text-sm mb-4 {{ $lastResult->status === 'online' ? 'text-green-700' : 'text-gray-700' }}">
                {{ $lastResult->message }}
            </p>

            @if($lastResult->status !== 'offline')
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white p-2 rounded shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Perte</div>
                        <div class="text-sm font-bold">{{ $lastResult->packet_loss_pct }}%</div>
                    </div>
                    <div class="bg-white p-2 rounded shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Moyenne</div>
                        <div class="text-sm font-bold">{{ $lastResult->avg_latency_ms }}ms</div>
                    </div>
                    <div class="bg-white p-2 rounded shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Max</div>
                        <div class="text-sm font-bold">{{ $lastResult->max_latency_ms }}ms</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
