<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrinterCounter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'recorded_at',
        'total_pages',
        'color_pages',
        'bw_pages',
        'toner_black_pct',
        'toner_cyan_pct',
        'toner_magenta_pct',
        'toner_yellow_pct',
        'printer_status',
        'paper_level',
        'consumables',
        'a3_pages',
        'a4_pages',
        'is_consumption_snapshot',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recorded_at' => 'datetime',
        'total_pages' => 'integer',
        'color_pages' => 'integer',
        'bw_pages' => 'integer',
        'toner_black_pct' => 'decimal:2',
        'toner_cyan_pct' => 'decimal:2',
        'toner_magenta_pct' => 'decimal:2',
        'toner_yellow_pct' => 'decimal:2',
        'consumables' => 'array',
        'a3_pages' => 'integer',
        'a4_pages' => 'integer',
        'is_consumption_snapshot' => 'boolean',
    ];

    /**
     * Get the device associated with these counters.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
