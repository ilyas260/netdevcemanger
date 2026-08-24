<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\ErrorLog;
use App\Services\SnmpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FetchSnmpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Device $device;

    /**
     * Create a new job instance.
     */
    public function __construct(Device $device)
    {
        $this->device = $device;
        $this->queue = 'scan';
    }

    /**
     * Execute the job.
     */
    public function handle(SnmpService $snmpService): void
    {
        $snmpService->fetchCounters($this->device);
    }

}
