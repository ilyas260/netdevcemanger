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
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('nd_technique')->nullable()->after('name');
            $table->string('debit_cible')->nullable()->after('nd_technique');
            $table->string('hostname')->nullable()->after('router_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['nd_technique', 'debit_cible', 'hostname']);
        });
    }
};
