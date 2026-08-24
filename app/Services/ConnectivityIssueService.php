<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Device;
use App\Models\ErrorLog;
use App\Models\AlertRecipient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * Service de gestion des problèmes de connectivité des agences.
 * 
 * Responsabilités :
 * - Créer/mettre à jour les problèmes de connectivité
 * - Envoyer les emails d'alerte sans doublon
 * - Résourdre les problèmes quand la connexion revient
 */
class ConnectivityIssueService
{
    /**
     * Résout automatiquement les erreurs non résolues pour un device remis en ligne
     */
    public function resolveConnectivityIssuesForDevice(Device $device)
    {
        $now = Carbon::now();
        $autoUserId = null; // Remplacez par un ID système si besoin

        ErrorLog::where('device_id', $device->id)
            ->where('is_resolved', false)
            ->update([
                'is_resolved' => true,
                'resolved_at' => $now,
                'resolved_by' => $autoUserId,
                'resolution_note' => 'Résolution automatique : appareil de nouveau en ligne.',
            ]);
    }

    /**
     * Enregistre un problème de connectivité pour une agence
     * Crée un nouveau problème ou met à jour l'existant
     */
    public function recordAgencyConnectivityIssue(
        Agency $agency,
        string $issueType = 'Panne Agence',
        string $message = '',
        string $diagnosticInfo = ''
    ): ErrorLog
    {
        if (!$message) {
            $message = "Le routeur de l'agence {$agency->name} ({$agency->router_ip}) est injoignable.";
        }

        // Récupérer le routeur ou créer un Device de référence
        $routerDevice = Device::firstOrCreate(
            [
                'agency_id' => $agency->id,
                'ip_address' => $agency->router_ip,
            ],
            [
                'name' => "Routeur - {$agency->name}",
                'type' => 'routeur',
                'brand' => 'unknown',
                'model' => 'unknown',
                'is_active' => true,
                'status' => 'offline',
            ]
        );

        // Chercher un problème existant non résolu de ce type pour cette agence et ce routeur
        $existingIssue = ErrorLog::where('device_id', $routerDevice->id)
            ->where('error_type', $issueType)
            ->where('is_resolved', false)
            ->first();

        if ($existingIssue) {
            // Mettre à jour le problème existant sans renvoyer d'email si déjà envoyé
            $existingIssue->update([
                'logged_at' => Carbon::now(),
                'message' => $message . ($diagnosticInfo ? " | " . $diagnosticInfo : ""),
                'severity' => 'CRITICAL',
            ]);

            return $existingIssue;
        }

        // Créer un nouveau problème
        $issue = ErrorLog::create([
            'device_id' => $routerDevice->id,
            'logged_at' => Carbon::now(),
            'error_type' => $issueType,
            'severity' => 'CRITICAL',
            'message' => $message . ($diagnosticInfo ? " | " . $diagnosticInfo : ""),
            'source' => 'ping',
            'is_resolved' => false,
            'mail_sent' => false,
        ]);

        return $issue;
    }

    public function sendPendingAlerts(): void
    {
        $unsentIssues = ErrorLog::unsent()
            ->with(['device.agency'])
            ->get();

        if ($unsentIssues->isEmpty()) {
            return;
        }

        $recipients = AlertRecipient::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            Log::warning("No active alert recipients found for grouped alerts.");
            return;
        }

        // Pour les problèmes non résolus, on attend 5 minutes avant d'envoyer l'alerte
        $offlineIssues = $unsentIssues->where('is_resolved', false)
            ->filter(function ($issue) {
                return $issue->logged_at && $issue->logged_at->diffInMinutes(Carbon::now()) >= 5;
            });

        // Pour les problèmes résolus
        $resolvedIssues = collect();
        $issuesToMarkAsSent = collect();

        foreach ($unsentIssues->where('is_resolved', true) as $issue) {
            // Si le problème a été résolu en moins de 5 minutes (durée totale),
            // on ne notifie pas (ni panne ni résolution) pour éviter le spam.
            // On considère la durée entre logged_at et resolved_at.
            if ($issue->logged_at && $issue->resolved_at && $issue->logged_at->diffInMinutes($issue->resolved_at) < 5) {
                // On le marque juste comme envoyé sans envoyer d'email
                $issuesToMarkAsSent->push($issue);
            } else {
                $resolvedIssues->push($issue);
            }
        }

        try {
            // Envoi des problèmes (non connectés)
            if ($offlineIssues->isNotEmpty()) {
                foreach ($recipients as $recipient) {
                    Mail::to($recipient->email)->send(new \App\Mail\GroupedConnectivityAlert($offlineIssues, false));
                }
                Log::info("Grouped offline alert email sent for " . count($offlineIssues) . " issues.");
                $issuesToMarkAsSent = $issuesToMarkAsSent->merge($offlineIssues);
            }

            // Envoi des résolutions (rétablis)
            if ($resolvedIssues->isNotEmpty()) {
                foreach ($recipients as $recipient) {
                    Mail::to($recipient->email)->send(new \App\Mail\GroupedConnectivityAlert($resolvedIssues, true));
                }
                Log::info("Grouped resolution email sent for " . count($resolvedIssues) . " issues.");
                $issuesToMarkAsSent = $issuesToMarkAsSent->merge($resolvedIssues);
            }

            // Marquer comme envoyé uniquement ceux qui ont été traités
            foreach ($issuesToMarkAsSent as $issue) {
                $issue->update(['mail_sent' => true]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send grouped alert email: " . $e->getMessage());
        }
    }

    /**
     * Envoie un email contenant la liste de toutes les agences actuellement déconnectées
     * Généralement appelé lorsque la connexion Internet est rétablie.
     */
    public function sendAllOfflineAgenciesEmail(): void
    {
        // Récupérer toutes les agences ayant un routeur hors ligne
        $offlineAgencies = Agency::whereHas('devices', function ($query) {
            $query->where('type', 'routeur')
                  ->where('status', 'offline');
        })->with(['devices' => function ($query) {
            $query->where('type', 'routeur')->with(['errorLogs' => function ($q) {
                $q->where('is_resolved', false)->latest();
            }]);
        }])->get();

        $recipients = AlertRecipient::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            Log::warning("No active alert recipients found for offline agencies summary.");
            return;
        }

        try {
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(new \App\Mail\AllOfflineAgenciesAlert($offlineAgencies));
            }
            Log::info("All offline agencies summary email sent successfully upon internet restoration.");
        } catch (\Exception $e) {
            Log::error("Failed to send all offline agencies summary email: " . $e->getMessage());
        }
    }

    /**
     * Envoie un email d'alerte pour un problème de connectivité spécifique
     */
    public function sendAlertEmail(Agency $agency, ErrorLog $issue): bool
    {
        // Si l'email a déjà été envoyé, ne pas le renvoyer
        if ($issue->mail_sent) {
            return true;
        }

        $recipients = AlertRecipient::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            Log::warning("No active alert recipients found for agency {$agency->id}");
            return false;
        }

        $titleAlert = $issue->error_type;
        $diagnostic = $issue->message;

        try {
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)
                    ->send(new \App\Mail\DeviceStatusAlert(
                        $agency->name,
                        $agency->router_ip,
                        'offline',
                        'Agence',
                        $diagnostic
                    ));
            }

            // Marquer l'email comme envoyé
            $issue->update(['mail_sent' => true]);

            Log::info("Alert email sent for ErrorLog #{$issue->id} (Agency: {$agency->name})");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send alert email for ErrorLog #{$issue->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Résout un problème de connectivité quand la connexion revient
     */
    public function resolveConnectivityIssue(
        Agency $agency,
        string $issueType = 'Panne Agence',
        string $resolutionMessage = 'Connexion rétablie'
    ): ?ErrorLog
    {
        $routerDevice = Device::where('agency_id', $agency->id)
            ->where('ip_address', $agency->router_ip)
            ->first();

        if (!$routerDevice) {
            Log::warning("No router device found for agency {$agency->id}");
            return null;
        }

        // Chercher tous les problèmes non résolus de ce type
        $issues = ErrorLog::where('device_id', $routerDevice->id)
            ->where('error_type', $issueType)
            ->where('is_resolved', false)
            ->get();

        foreach ($issues as $issue) {
            $issue->update([
                'is_resolved' => true,
                'resolved_at' => Carbon::now(),
                'resolution_note' => $resolutionMessage,
                'resolved_by' => null, // Résolution automatique
                'mail_sent' => false, // Réinitialiser pour future alerte
            ]);

            // L'email de résolution n'est plus envoyé ici individuellement.
            // Il sera envoyé groupé par la commande SendPendingConnectivityAlerts.
        }

        return $issues->first();
    }

    /**
     * Récupère le diagnostic hiérarchique d'un problème de connectivité
     */
    public function getDiagnosticInfo(Agency $agency): string
    {
        $dnServer = '10.110.103.200'; // DNS_SERVER constant
        $gateway = '10.110.31.1';     // GATEWAY constant

        $pingService = app(PingService::class);

        if ($pingService->checkConnectivity($dnServer) === 'offline') {
            if ($pingService->checkConnectivity($gateway) === 'offline') {
                return "Diagnostic: Perte de connexion locale du serveur (Passerelle {$gateway} injoignable).";
            }
            return "Diagnostic: Panne réseau central (Serveur DNS {$dnServer} injoignable).";
        }

        return "Diagnostic: Problème de connectivité spécifique à l'agence {$agency->name}.";
    }

    /**
     * Identifie le type de problème et enregistre automatiquement
     */
    public function identifyAndRecord(Agency $agency): ErrorLog
    {
        $diagnosticInfo = $this->getDiagnosticInfo($agency);
        $issueType = 'Panne Agence';

        if (strpos($diagnosticInfo, 'serveur') !== false) {
            $issueType = 'Panne Serveur';
        } elseif (strpos($diagnosticInfo, 'réseau central') !== false) {
            $issueType = 'Panne Réseau Central';
        }

        return $this->recordAgencyConnectivityIssue($agency, $issueType, '', $diagnosticInfo);
    }

    /**
     * Envoie un email de résolution à tous les destinataires
     */
    public function sendResolutionEmail(ErrorLog $issue, string $resolutionNote, string $resolverName): bool
    {
        $agency = $issue->device?->agency;

        if (!$agency) {
            Log::warning("ErrorLog #{$issue->id} has no associated agency, cannot send resolution email.");
            return false;
        }

        $recipients = AlertRecipient::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            Log::warning("No active alert recipients found for resolution email of ErrorLog #{$issue->id}");
            return false;
        }

        try {
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)
                    ->send(new \App\Mail\IssueResolutionNotification(
                        $agency->name,
                        $agency->router_ip,
                        $issue->error_type,
                        $issue->message,
                        $resolutionNote,
                        $resolverName,
                        $issue->solution_type ?? '',
                        \App\Models\ErrorLog::getSolutionTypes()[$issue->solution_type ?? ''] ?? ''
                    ));
            }

            // Marquer l'email comme envoyé
            $issue->update(['mail_sent' => true]);

            Log::info("Resolution email sent for ErrorLog #{$issue->id} (Agency: {$agency->name})");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send resolution email for ErrorLog #{$issue->id}: " . $e->getMessage());
            return false;
        }
    }
}
