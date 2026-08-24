<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PingController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrinterInfoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('agencies.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scanner', \App\Livewire\NetworkScanner::class)->name('scanner');

    // Devices (Gestion des appareils)
    Route::resource('devices', DeviceController::class);

    // Connectivité (Ping) - lecture (historique) accessible à tous, actions réservées
    Route::get('/devices/{device}/ping-history', [PingController::class, 'history'])->name('devices.ping-history');

    // Journaux d'erreurs
    Route::get('/error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
    Route::get('/error-logs/export', [ErrorLogController::class, 'export'])->name('error-logs.export');
    
    Route::middleware(['role:admin|technicien'])->group(function () {
        Route::patch('/error-logs/{errorLog}/resolve', [ErrorLogController::class, 'resolve'])->name('error-logs.resolve');
    });

    // Routes réservées aux Techniciens et Admins (pas consultants)
    Route::middleware(['role:admin|technicien'])->group(function () {
        Route::post('/devices/{device}/ping', [PingController::class, 'launch'])->name('devices.ping');
        Route::post('/devices/{device}/restore', [DeviceController::class, 'restore'])->name('devices.restore');
        Route::post('/devices/{device}/sync-snmp', [DeviceController::class, 'syncSnmp'])->name('devices.sync-snmp');
    });

    // Rapports & Statistiques
    Route::get('/analytics', [App\Http\Controllers\GlobalStatisticsController::class, 'index'])->name('statistics.global');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    Route::get('/statistics/toner', function() {
        return view('statistics.toner');
    })->name('statistics.toner');

    Route::get('/statistics/connectivity', function() {
        return view('statistics.connectivity');
    })->name('statistics.connectivity');

    // Agences
    Route::get('/agencies', \App\Livewire\AgencyManager::class)->name('agencies.index');
    Route::get('/agencies/{id}', \App\Livewire\AgencyDashboard::class)->name('agencies.show');
    Route::post('/api/agencies/quick-create', function(\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'router_ip' => 'required|ip|unique:agencies,router_ip',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'nd_technique' => 'nullable|string|max:255',
            'debit_cible' => 'nullable|string|max:255',
            'hostname' => 'nullable|string|max:255',
        ]);
        
        $agency = \App\Models\Agency::create($validated);
        
        return response()->json([
            'success' => true,
            'agency' => $agency
        ]);
    });

    // Imprimante (Test SNMP)
    Route::get('/printer', [PrinterInfoController::class, 'index'])->name('printer.info');
    Route::post('/printer/save', [PrinterInfoController::class, 'store'])->name('printer.store');

    // Gestion des Alertes (Emails) - Admin uniquement
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/alert-recipients', \App\Livewire\AlertRecipientManager::class)->name('alerts.recipients');
        Route::get('/settings/diagnostics', \App\Livewire\DiagnosticManager::class)->name('settings.diagnostics');
    });

    // Profil Utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administration Utilisateurs
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', \App\Livewire\UserManager::class)->name('admin.users');
    });
});

require __DIR__.'/auth.php';
