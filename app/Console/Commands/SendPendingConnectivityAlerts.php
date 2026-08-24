<?php

namespace App\Console\Commands;

use App\Services\ConnectivityIssueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingConnectivityAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'connectivity:send-alerts {--force : Force sending even if already sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending connectivity alert emails to recipients';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking internet connection and sending pending connectivity alerts...');

        $connectivityService = new ConnectivityIssueService();

        // Envoi des alertes en attente
        try {
            $connectivityService->sendPendingAlerts();
            
            $this->info('✓ All pending connectivity alerts have been processed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error sending connectivity alerts: ' . $e->getMessage());
            Log::error('Command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
