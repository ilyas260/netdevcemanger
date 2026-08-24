<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ErrorLog extends Model
{
    use HasFactory;

    /**
     * Types de solutions prédéfinis pour les statistiques annuelles.
     */
    public const SOLUTION_TYPES = [
        'retablissement_auto'         => 'Rétablissement automatique de la connexion',
        'redemarrage_routeur'         => 'Redémarrage du routeur',
        'probleme_operateur'          => 'Problème opérateur télécoms (signalement)',
        'probleme_cablage'            => 'Problème câblage / infrastructure',
        'probleme_configuration'      => 'Problème de configuration réseau',
        'coupure_electrique'          => 'Coupure électrique (alimentation)',
        'panne_materiel'              => 'Panne matériel (remplacement)',
        'probleme_dns_ip'             => 'Problème DNS / Adresse IP',
        'vpn_retabli'                 => 'Accès VPN rétabli',
        'mise_a_jour'                 => 'Mise à jour / maintenance programmée',
        'autre'                       => 'Autre (voir note de résolution)',
    ];

    /**
     * Get solution types dynamically from settings (with fallback to constants).
     */
    public static function getSolutionTypes(): array
    {
        try {
            $stored = \App\Models\Setting::get('diagnostic_types');
            if ($stored) {
                $decoded = json_decode($stored, true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
            // fallback to constants
        }
        return self::SOLUTION_TYPES;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'logged_at',
        'error_type',
        'severity',
        'message',
        'source',
        'mail_sent',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_note',
        'solution_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'logged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean',
        'mail_sent' => 'boolean',
    ];

    /**
     * Scope a query to only include unresolved errors.
     */
    public function scopeUnresolved(Builder $query): void
    {
        $query->where('is_resolved', false);
    }

    /**
     * Scope a query to only include errors that haven't been sent by mail yet.
     */
    public function scopeUnsent(Builder $query): void
    {
        $query->where('mail_sent', false);
    }

    /**
     * Scope a query to only include connectivity issues by agency.
     */
    public function scopeConnectivityIssues(Builder $query): void
    {
        $query->where('error_type', 'Panne Agence')
            ->orWhere('error_type', 'SNMP Inaccessible')
            ->orWhere('error_type', 'Network Unavailable');
    }

    /**
     * Get the device associated with the error log.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the user who resolved the error.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
