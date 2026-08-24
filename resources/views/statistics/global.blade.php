<x-app-layout>
    <div class="p-6">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Centre d'<span class="text-indigo-600">Analyses Globales</span></h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Visualisez la performance et la fiabilité de votre infrastructure réseau.</p>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200">
                <div class="text-sm font-bold uppercase opacity-80 tracking-widest">Disponibilité Globale (30j)</div>
                <div class="text-5xl font-black mt-2">{{ $uptimePct }}%</div>
                <div class="mt-4 text-xs font-medium bg-white/20 inline-block px-2 py-1 rounded">SLA Performance</div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Incidents Détectés (7j)</div>
                <div class="text-4xl font-black text-slate-800 mt-2">{{ $incidentsTrend->sum('total') }}</div>
                <div class="mt-4 text-xs font-medium text-red-600">Requiert attention immédiate</div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Parc Supervisé</div>
                <div class="text-4xl font-black text-slate-800 mt-2">{{ \App\Models\Device::count() }}</div>
                <div class="mt-4 text-xs font-medium text-indigo-600">Actifs et configurés</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Latency by Type -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Latence Moyenne par Type (ms)
                </h3>
                <div class="h-64">
                    <canvas id="latencyChart"></canvas>
                </div>
            </div>

            <!-- Incident Trend -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Evolution des Incidents (7j)
                </h3>
                <div class="h-64">
                    <canvas id="incidentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Brand Distribution -->
            <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6">Top Marques</h3>
                <div class="h-64">
                    <canvas id="brandChart"></canvas>
                </div>
            </div>

            <!-- Detailed Uptime Status -->
            <div class="lg:col-span-2 bg-slate-900 rounded-2xl p-8 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-2 text-indigo-400">Santé du Réseau</h3>
                    <p class="text-slate-400 max-w-md">L'analyse des pings indique une stabilité de {{ $uptimePct }}% sur l'ensemble de vos passerelles et périphériques critiques.</p>
                    <div class="mt-6 flex gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold italic">{{ \App\Models\PingLog::where('status', 'offline')->count() }}</div>
                            <div class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Échecs</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold italic">{{ \App\Models\PingLog::where('status', 'online')->count() }}</div>
                            <div class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Réussites</div>
                        </div>
                    </div>
                </div>
                <div class="w-48 h-48 relative">
                    <canvas id="uptimeChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center font-black text-2xl">
                        {{ $uptimePct }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Latency Chart (Bar)
        new Chart(document.getElementById('latencyChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($latencyByType->pluck('type')) !!},
                datasets: [{
                    label: 'Latence (ms)',
                    data: {!! json_encode($latencyByType->pluck('avg_latency')) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 8
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Incident Trend (Line)
        new Chart(document.getElementById('incidentChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($incidentsTrend->pluck('date')) !!},
                datasets: [{
                    label: 'Erreurs',
                    data: {!! json_encode($incidentsTrend->pluck('total')) !!},
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Brand Chart (Pie or Horizontal Bar)
        new Chart(document.getElementById('brandChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($brandDistribution->pluck('brand')) !!},
                datasets: [{
                    data: {!! json_encode($brandDistribution->pluck('total')) !!},
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#0ea5e9', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });

        // Uptime Chart (Semi-doughnut)
        new Chart(document.getElementById('uptimeChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $uptimePct }}, {{ 100 - $uptimePct }}],
                    backgroundColor: ['#4f46e5', '#1e293b'],
                    borderWidth: 0,
                    circumference: 360,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '85%', plugins: { legend: { display: false } } }
        });
    </script>
    @endpush
</x-app-layout>
