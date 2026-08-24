<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Historique Ping : {{ $device->name }}
            </h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-mono bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full border border-indigo-100">
                    {{ $device->ip_address }}
                </span>
                <a href="{{ route('devices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Ping Form Component -->
        @livewire('ping-form', ['device' => $device])

        <!-- Table History -->
        <div class="bg-white shadow-sm sm:rounded-lg border overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Logs de connectivité récents</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Latence Moy.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Déclenchement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $log->tested_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status === 'online' ? 'bg-green-100 text-green-800' : ($log->status === 'offline' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800') }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                {{ $log->avg_latency_ms ? $log->avg_latency_ms . ' ms' : '--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-{{ $log->packet_loss_pct > 0 ? 'red' : 'green' }}-600 font-bold">
                                {{ (float)$log->packet_loss_pct }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs px-2 py-0.5 rounded {{ $log->triggered_by === 'manual' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($log->triggered_by) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">Aucun historique de ping disponible pour cet appareil.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 bg-gray-50">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
