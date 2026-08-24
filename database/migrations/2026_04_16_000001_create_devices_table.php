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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['imprimante', 'routeur', 'switch', 'serveur', 'pc']);
            $table->string('brand', 80);
            $table->string('model', 100);
            $table->string('ip_address', 15)->unique();
            $table->string('location')->nullable();
            $table->string('snmp_community')->nullable();
            $table->tinyInteger('snmp_version')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
