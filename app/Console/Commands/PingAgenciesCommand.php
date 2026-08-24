<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agency;
use App\Jobs\AgencyPingJob;

class PingAgenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping:agencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping all agencies in the background';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agencies = Agency::all();
        $this->info("Dispatching ping jobs for " . $agencies->count() . " agencies...");

        $agencies->each(function ($agency) {
            AgencyPingJob::dispatch($agency)->onQueue('scan');
        });

        $this->info("Ping jobs dispatched to the 'scan' queue.");
    }
}
