<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanResult extends Model
{
    protected $fillable = [
        'scan_id',
        'ip_address',
        'hostname',
        'mac_address',
        'vendor',
        'exists_in_db',
        'existing_name',
    ];
}
