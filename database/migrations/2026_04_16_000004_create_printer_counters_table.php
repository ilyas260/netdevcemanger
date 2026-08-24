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
        Schema::create('printer_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->timestamp('recorded_at');
            $table->integer('total_pages')->unsigned();
            $table->integer('color_pages')->unsigned()->default(0);
            $table->integer('bw_pages')->unsigned()->default(0);
            $table->decimal('toner_black_pct', 5, 2)->nullable();
            $table->decimal('toner_cyan_pct', 5, 2)->nullable();
            $table->decimal('toner_magenta_pct', 5, 2)->nullable();
            $table->decimal('toner_yellow_pct', 5, 2)->nullable();
            $table->string('printer_status', 80)->nullable();
            $table->string('paper_level', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_counters');
    }
};
