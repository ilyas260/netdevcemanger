<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TonerAlert extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'toner_color',
        'level_pct',
        'threshold_pct',
        'alerted_at',
        'is_sent',
        'is_resolved',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'alerted_at' => 'datetime',
        'level_pct' => 'decimal:2',
        'threshold_pct' => 'decimal:2',
        'is_sent' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the device associated with this toner alert.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
