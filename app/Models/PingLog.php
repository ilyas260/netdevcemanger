<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Agency;

class PingLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'agency_id',
        'tested_at',
        'duration_sec',
        'packets_sent',
        'packets_received',
        'packet_loss_pct',
        'avg_latency_ms',
        'min_latency_ms',
        'max_latency_ms',
        'status',
        'message',
        'triggered_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tested_at' => 'datetime',
        'packet_loss_pct' => 'decimal:2',
        'avg_latency_ms' => 'decimal:3',
        'min_latency_ms' => 'decimal:3',
        'max_latency_ms' => 'decimal:3',
    ];

    /**
     * Scope a query to only include offline results.
     */
    public function scopeOffline(Builder $query): void
    {
        $query->where('status', 'offline');
    }

    /**
     * Get the device that owns the ping log.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the agency that owns the ping log.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
