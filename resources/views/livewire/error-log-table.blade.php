<div class="space-y-4">
    <!-- Filtres -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-lg shadow-sm border">
        <select wire:model.live="agencyId" class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Toutes les agences</option>
            @foreach($agencies as $agency)
                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="severity" class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Toutes sévérités</option>
            <option value="CRITICAL">Critique</option>
            <option value="ERROR">Erreur</option>
            <option value="WARNING">Avertissement</option>
            <option value="INFO">Info</option>
        </select>

        <select wire:model.live="status" class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="unresolved">Non résolues</option>
            <option value="resolved">Résolues</option>
            <option value="all">Tout l'historique</option>
        </select>

        <a href="{{ route('error-logs.export') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Exporter CSV
        </a>
    </div>

    <!-- Tableau -->
    <div class="bg-white shadow-sm sm:rounded-lg border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sévérité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Problème / Diagnostic</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" wire:poll.10s>
                @forelse($logs as $log)
                    <livewire:error-log-row :log="$log" wire:key="error-log-{{ $log->id }}" />
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 font-medium">Aucun log d'erreur.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>
</div>
