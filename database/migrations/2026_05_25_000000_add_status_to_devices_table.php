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
        if (!Schema::hasColumn('devices', 'status')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('status')->default('unknown')->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('devices', 'status')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
