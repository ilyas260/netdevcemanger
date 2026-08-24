<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Agency;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('network_address')->nullable()->after('router_ip');
        });

        // Populate existing records
        foreach (Agency::all() as $agency) {
            if ($agency->router_ip && preg_match('/^(\d+\.\d+\.\d+)\./', $agency->router_ip, $matches)) {
                $agency->network_address = $matches[1] . '.0/24';
                $agency->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('network_address');
        });
    }
};
