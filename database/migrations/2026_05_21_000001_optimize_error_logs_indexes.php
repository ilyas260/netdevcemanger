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
        Schema::table('error_logs', function (Blueprint $table) {
            // Ajouter des indices pour optimiser les requêtes fréquentes
            if (!Schema::hasIndex('error_logs', 'idx_unsent_unresolved')) {
                $table->index(['mail_sent', 'is_resolved', 'error_type'], 'idx_unsent_unresolved');
            }

            if (!Schema::hasIndex('error_logs', 'idx_device_unresolved')) {
                $table->index(['device_id', 'is_resolved'], 'idx_device_unresolved');
            }

            if (!Schema::hasIndex('error_logs', 'idx_connectivity_unresolved')) {
                $table->index(['error_type', 'is_resolved', 'logged_at'], 'idx_connectivity_unresolved');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            if (Schema::hasIndex('error_logs', 'idx_unsent_unresolved')) {
                $table->dropIndex('idx_unsent_unresolved');
            }

            if (Schema::hasIndex('error_logs', 'idx_device_unresolved')) {
                $table->dropIndex('idx_device_unresolved');
            }

            if (Schema::hasIndex('error_logs', 'idx_connectivity_unresolved')) {
                $table->dropIndex('idx_connectivity_unresolved');
            }
        });
    }
};
