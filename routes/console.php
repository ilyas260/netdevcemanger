<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Device;
use App\Models\ErrorLog;
use App\Jobs\PingDeviceJob;
use App\Jobs\FetchSnmpJob;
use App\Services\ReportService;
use Carbon\Carbon;

Schedule::call(function () {
    // Ping des appareils individuels
    Device::active()->get()->each(function ($device) {
        PingDeviceJob::dispatch($device, 4)->onQueue('scan');
    });

    // Ping des routeurs d'agences
    \App\Models\Agency::all()->each(function ($agency) {
        \App\Jobs\AgencyPingJob::dispatch($agency)->onQueue('scan');
    });
})->cron('*/' . (\App\Models\Setting::get('ping_interval', 5)) . ' * * * *');

// Envoi des emails d'alerte de connectivité en attente
Schedule::command('connectivity:send-alerts')->everyTwoMinutes();


// Le rapport d'alerte groupé toutes les 5 minutes a été supprimé à la demande de l'utilisateur
// pour ne pas spammer. Les alertes sont gérées par connectivity:send-alerts une seule fois.


// Relevé SNMP toutes les 15 minutes pour les imprimantes
Schedule::call(function () {
    Device::active()->where('type', 'imprimante')->get()->each(function ($device) {
        FetchSnmpJob::dispatch($device);
    });
})->everyFifteenMinutes();

// Nettoyage des appareils supprimés depuis plus de 30 jours (SoftDelete)
Schedule::call(function () {
    Device::onlyTrashed()
        ->where('deleted_at', '<', now()->subDays(30))
        ->forceDelete();
})->dailyAt('02:00');

// Purge des logs d'erreurs de plus d'un an
Schedule::call(function () {
    ErrorLog::where('logged_at', '<', now()->subYear())->delete();
})->weeklyOn(0, '03:00');

// Rapport hebdomadaire automatique par email l'admin
Schedule::call(function (ReportService $reportService) {
    $start = now()->subWeek();
    $end = now();
    $data = $reportService->generate($start, $end);
    $reportService->sendByEmail($data, 'admin@netdevice.local');
})->mondays()->at('07:00');

