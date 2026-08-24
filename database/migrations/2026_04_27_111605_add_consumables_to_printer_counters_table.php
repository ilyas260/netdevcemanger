<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('printer_counters', function (Blueprint $table) {
            $table->json('consumables')->nullable()->after('toner_yellow_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printer_counters', function (Blueprint $table) {
            $table->dropColumn('consumables');
        });
    }
};
