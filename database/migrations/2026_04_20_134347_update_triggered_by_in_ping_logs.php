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
        Schema::table('ping_logs', function (Blueprint $table) {
            $table->string('triggered_by', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ping_logs', function (Blueprint $table) {
            $table->enum('triggered_by', ['manual', 'scheduler'])->default('manual')->change();
        });
    }
};
