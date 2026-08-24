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
            $table->integer('a3_pages')->unsigned()->nullable()->after('bw_pages');
            $table->integer('a4_pages')->unsigned()->nullable()->after('a3_pages');
            $table->boolean('is_consumption_snapshot')->default(false)->after('paper_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printer_counters', function (Blueprint $table) {
            $table->dropColumn(['a3_pages', 'a4_pages', 'is_consumption_snapshot']);
        });
    }
};
