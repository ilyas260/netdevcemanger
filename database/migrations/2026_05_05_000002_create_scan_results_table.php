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
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->string('scan_id')->index();
            $table->string('ip_address');
            $table->string('hostname')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('vendor')->nullable();
            $table->boolean('exists_in_db')->default(false);
            $table->string('existing_name')->nullable();
            $table->timestamps();
            
            // Index pour suppression rapide
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
