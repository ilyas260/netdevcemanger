<?php

namespace App\Traits;

use App\Models\ErrorLog;
use App\Services\ConnectivityIssueService;
use Carbon\Carbon;

/**
 * Trait pour gérer les problèmes de connectivité des agences.
 * À utiliser dans le modèle Agency si nécessaire.
 */
trait ManagesConnectivityIssues
{
    /**
     * Récupère les problèmes de connectivité non résolus pour cette agence
     */
    public function getUnresolvedConnectivityIssues()
    {
        return ErrorLog::whereHas('device', function ($query) {
            $query->where('agency_id', $this->id);
        })
            ->where('is_resolved', false)
            ->get();
    }

    /**
     * Vérifie s'il y a des problèmes de connectivité actifs
     */
    public function hasActiveConnectivityIssues(): bool
    {
        return $this->getUnresolvedConnectivityIssues()->isNotEmpty();
    }

    /**
     * Récupère le dernier problème de connectivité non résolu
     */
    public function getLatestUnresolvedIssue(): ?ErrorLog
    {
        return ErrorLog::whereHas('device', function ($query) {
            $query->where('agency_id', $this->id);
        })
            ->where('is_resolved', false)
            ->latest('logged_at')
            ->first();
    }

    /**
     * Enregistre un problème de connectivité pour cette agence
     */
    public function recordConnectivityIssue(
        string $issueType = 'Panne Agence',
        string $message = ''
    ): ErrorLog
    {
        $service = app(ConnectivityIssueService::class);
        return $service->recordAgencyConnectivityIssue($this, $issueType, $message);
    }

    /**
     * Résout les problèmes de connectivité pour cette agence
     */
    public function resolveConnectivityIssues(string $resolutionMessage = 'Connexion rétablie'): void
    {
        $service = app(ConnectivityIssueService::class);
        $service->resolveConnectivityIssue($this, 'Panne Agence', $resolutionMessage);
        $service->resolveConnectivityIssue($this, 'Panne Serveur', $resolutionMessage);
        $service->resolveConnectivityIssue($this, 'Panne Réseau Central', $resolutionMessage);
    }

    /**
     * Envoie un email d'alerte pour le problème de connectivité
     */
    public function sendConnectivityAlert(ErrorLog $issue): bool
    {
        $service = app(ConnectivityIssueService::class);
        return $service->sendAlertEmail($this, $issue);
    }

    /**
     * Obtient l'historique complet des problèmes de connectivité
     */
    public function getConnectivityIssueHistory(int $limit = 20)
    {
        return ErrorLog::whereHas('device', function ($query) {
            $query->where('agency_id', $this->id);
        })
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtient les statistiques des problèmes de connectivité
     */
    public function getConnectivityStats(Carbon $start, Carbon $end): array
    {
        $issues = ErrorLog::whereHas('device', function ($query) {
            $query->where('agency_id', $this->id);
        })
            ->whereBetween('logged_at', [$start, $end])
            ->get();

        return [
            'total_issues' => $issues->count(),
            'unresolved' => $issues->where('is_resolved', false)->count(),
            'critical' => $issues->where('severity', 'CRITICAL')->count(),
            'avg_resolution_time' => $issues->where('is_resolved', true)->average(function ($issue) {
                return $issue->resolved_at->diffInMinutes($issue->logged_at);
            }),
        ];
    }
}
