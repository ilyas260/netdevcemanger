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
        Schema::create('ping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->timestamp('tested_at');
            $table->smallInteger('duration_sec');
            $table->integer('packets_sent');
            $table->integer('packets_received');
            $table->decimal('packet_loss_pct', 5, 2);
            $table->decimal('avg_latency_ms', 8, 3)->nullable();
            $table->decimal('min_latency_ms', 8, 3)->nullable();
            $table->decimal('max_latency_ms', 8, 3)->nullable();
            $table->enum('status', ['online', 'offline', 'unstable', 'slow']);
            $table->text('message')->nullable();
            $table->enum('triggered_by', ['manual', 'scheduler'])->default('manual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ping_logs');
    }
};
