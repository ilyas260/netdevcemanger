<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute agency_id à ping_logs pour enregistrer les pings des agences.
     * Rend device_id nullable pour permettre les logs sans appareil associé.
     */
    public function up(): void
    {
        Schema::table('ping_logs', function (Blueprint $table) {
            // Rendre device_id nullable (un ping peut être pour une agence, pas un appareil)
            $table->foreignId('device_id')->nullable()->change();

            // Ajouter agency_id nullable
            $table->foreignId('agency_id')
                  ->nullable()
                  ->after('device_id')
                  ->constrained('agencies')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ping_logs', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropColumn('agency_id');
            $table->foreignId('device_id')->nullable(false)->change();
        });
    }
};
