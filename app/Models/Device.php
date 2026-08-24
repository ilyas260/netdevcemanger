<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'brand',
        'model',
        'ip_address',
        'location',
        'agency_id',
        'snmp_community',
        'snmp_version',
        'is_active',
        'status',
        'last_seen_at',
        'notes',
    ];

    /**
     * Get the agency that owns the device.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'snmp_version' => 'integer',
        'snmp_community' => 'encrypted', // Sécurité : communauté chiffrée en BDD
    ];

    /**
     * Scope a query to only include active devices.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Get the ping logs for the device.
     */
    public function pingLogs(): HasMany
    {
        return $this->hasMany(PingLog::class);
    }

    /**
     * Get the error logs for the device.
     */
    public function errorLogs(): HasMany
    {
        return $this->hasMany(ErrorLog::class);
    }

    /**
     * Get the printer counters for the device.
     */
    public function printerCounters(): HasMany
    {
        return $this->hasMany(PrinterCounter::class);
    }

    /**
     * Get the toner alerts for the device.
     */
    public function tonerAlerts(): HasMany
    {
        return $this->hasMany(TonerAlert::class);
    }
}
