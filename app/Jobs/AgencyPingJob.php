<?php

namespace App\Jobs;

use App\Models\Agency;
use App\Services\PingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AgencyPingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Agency $agency;
    protected int $packets;

    /**
     * Create a new job instance.
     */
    public function __construct(Agency $agency, int $packets = 4)
    {
        $this->agency = $agency;
        $this->packets = $packets;
        $this->queue = 'scan';
    }

    /**
     * Execute the job.
     */
    public function handle(PingService $pingService): void
    {
        $pingService->executeAgencyPing($this->agency, $this->packets);
    }
}
