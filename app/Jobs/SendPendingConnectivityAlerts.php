<?php

namespace App\Jobs;

use App\Services\ConnectivityIssueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job pour envoyer les emails d'alerte en attente de connectivité
 * 
 * Exécuté périodiquement pour traiter les alertes de manière asynchrone
 */
class SendPendingConnectivityAlerts implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connectivityService = new ConnectivityIssueService();
        
        try {
            $connectivityService->sendPendingAlerts();
            Log::info('Pending connectivity alerts sent successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send pending connectivity alerts: ' . $e->getMessage());
            throw $e;
        }
    }
}
