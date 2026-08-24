<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\ManagesConnectivityIssues;

class Agency extends Model
{
    use HasFactory, SoftDeletes, ManagesConnectivityIssues;

    protected $fillable = [
        'name',
        'router_ip',
        'network_address',
        'location',
        'phone',
        'status',
        'last_ping_at',
        'nd_technique',
        'debit_cible',
        'hostname',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
    ];

    /**
     * Get the devices for the agency.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get the online status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'online' => 'green',
            'offline' => 'red',
            'slow' => 'yellow',
            'unstable' => 'orange',
            default => 'gray',
        };
    }
}
