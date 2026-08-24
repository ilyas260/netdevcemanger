<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\ErrorLog;
use App\Services\PingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PingDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Device $device;
    protected int $packets;

    /**
     * Create a new job instance.
     */
    public function __construct(Device $device, int $packets = 4)
    {
        $this->device = $device;
        $this->packets = $packets;
        $this->queue = 'scan';
    }

    /**
     * Execute the job.
     */
    public function handle(PingService $pingService): void
    {
        $pingService->executePing($this->device, $this->packets);
    }

}
