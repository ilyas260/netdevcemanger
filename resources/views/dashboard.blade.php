<x-app-layout>
    <x-slot name="header">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Supervision <span class="text-indigo-600">Réseau</span></h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">État complet de vos équipements en temps réel.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Configuration & Monitoring -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                @livewire('supervision-settings')
            </div>
            <div class="lg:col-span-2">
                <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg overflow-hidden relative">
                    <div class="relative z-10">
                        <h3 class="text-xl font-bold mb-2">Statut de la Surveillance</h3>
                        <p class="text-indigo-100 text-sm mb-4">Le système interroge automatiquement vos équipements selon l'intervalle défini à gauche.</p>
                        <div class="flex gap-4">
                            <div class="bg-white/20 px-4 py-2 rounded-lg backdrop-blur-sm">
                                <span class="block text-[10px] uppercase font-bold text-indigo-200">Dernier Scan</span>
                                <span class="font-bold">{{ now()->translatedFormat('H:i') }}</span>
                            </div>
                            <div class="bg-white/20 px-4 py-2 rounded-lg backdrop-blur-sm">
                                <span class="block text-[10px] uppercase font-bold text-indigo-200">Agences Actives</span>
                                <span class="font-bold">{{ \App\Models\Agency::count() }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Subtle background decoration -->
                    <div class="absolute -right-4 -bottom-4 opacity-20 transform rotate-12">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"></path></svg>
                    </div>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Diagramme par Type -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow duration-300">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    Répartition du Parc
                </h3>
                <div class="h-64">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>

            <!-- État du réseau -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow duration-300">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    État de Santé Réseau
                </h3>
                <div class="h-64 flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>


    </div>

    @push('scripts')
    <script>
        // Chart Type Distribution
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($chartData['device_types']->pluck('type')) !!},
                datasets: [{
                    data: {!! json_encode($chartData['device_types']->pluck('total')) !!},
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#64748b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart Status Distribution
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['En ligne', 'Hors ligne', 'Inactif'],
                datasets: [{
                    data: [
                        {{ $chartData['status_distribution']['online'] }},
                        {{ $chartData['status_distribution']['offline'] }},
                        {{ $chartData['status_distribution']['inactive'] }}
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#cbd5e1'],
                    borderWidth: 0,
                    weight: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
    @endpush

</x-app-layout>
