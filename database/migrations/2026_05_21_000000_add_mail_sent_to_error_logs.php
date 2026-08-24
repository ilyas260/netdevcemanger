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
            // Ajouter la colonne mail_sent
            if (!Schema::hasColumn('error_logs', 'mail_sent')) {
                $table->boolean('mail_sent')->default(false)->after('source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            if (Schema::hasColumn('error_logs', 'mail_sent')) {
                $table->dropColumn('mail_sent');
            }
        });
    }
};
