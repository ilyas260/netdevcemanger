<?php

namespace App\Livewire;

use App\Models\ErrorLog;
use App\Services\ConnectivityIssueService;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Reactive;

class ErrorLogRow extends Component
{
    public ErrorLog $log;
    
    public $showResolveModal = false;
    public $showEmailModal = false;
    public $resolution_note = '';
    public $solution_type = '';
    public $editingDiagnostic = false;
    public $diagnosticSaved = false;
    public $custom_solution = '';


    public function mount()
    {
        $this->solution_type = $this->log->solution_type ?? '';
        $this->editingDiagnostic = empty($this->log->solution_type);
    }

    public function getStatusDisplayProperty()
    {
        return $this->log->is_resolved ? 'Résolu' : 'Non résolu';
    }

    public function getEmailDisplayProperty()
    {
        return $this->log->mail_sent ? 'Envoyé' : 'Non envoyé';
    }

    public function render()
    {
        return view('livewire.error-log-row', [
            'emailDisplay' => $this->emailDisplay,
            'statusDisplay' => $this->statusDisplay,
        ]);
    }

    public function openResolveModal()
    {
        $this->showResolveModal = true;
        $this->resolution_note = '';
        $this->solution_type = '';

    }

    public function closeResolveModal()
    {
        $this->showResolveModal = false;
        $this->resolution_note = '';
        $this->solution_type = '';

    }

    public function openEmailModal()
    {
        $this->showEmailModal = true;
    }

    public function closeEmailModal()
    {
        $this->showEmailModal = false;
    }

    public function resolveError()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->validate([
            'solution_type'   => 'required|string',
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        $solutionLabel = \App\Models\ErrorLog::getSolutionTypes()[$this->solution_type] ?? $this->solution_type;
        $noteToSave    = $this->resolution_note ?: $solutionLabel;

        $this->log->update([
            'is_resolved'     => true,
            'resolved_at'     => Carbon::now(),
            'resolved_by'     => auth()->id(),
            'solution_type'   => $this->solution_type,
            'resolution_note' => $noteToSave,
            'mail_sent'       => false,
        ]);

        // Envoyer automatiquement l'email de résolution dans un tableau groupé
        try {
            $service = new ConnectivityIssueService();
            $service->sendPendingAlerts();
            Log::info("Grouped resolution email sent automatically for ErrorLog #{$this->log->id}");
            session()->flash('success', "Erreur #{$this->log->id} résolue et email groupé envoyé avec succès.");
        } catch (\Exception $e) {
            Log::error("Failed to send grouped resolution email for ErrorLog #{$this->log->id}: " . $e->getMessage());
            session()->flash('success', "Erreur #{$this->log->id} résolue. L'email groupé sera envoyé dès que la connexion internet sera rétablie.");
        }

        $this->log->refresh();
        $this->closeResolveModal();
    }

    public function saveDiagnosticConfirmed()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);

        $valueToSave = ($this->solution_type === 'autre' && !empty($this->custom_solution))
            ? $this->custom_solution
            : $this->solution_type;

        $this->validate(['solution_type' => 'required|string'], [
            'solution_type.required' => 'Veuillez sélectionner un diagnostic.',
        ]);

        $this->log->update(['solution_type' => $valueToSave]);
        $this->log->refresh();

        $this->solution_type = $valueToSave;
        $this->editingDiagnostic = false;
        $this->diagnosticSaved = true;
        $this->custom_solution = '';

        // Masquer le badge "Enregistré" après 3 secondes
        $this->dispatch('diagnostic-saved-' . $this->log->id);
    }

    public function startEditDiagnostic()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->solution_type = $this->log->solution_type ?? '';
        $this->editingDiagnostic = true;
        $this->diagnosticSaved = false;
    }

    public function cancelEditDiagnostic()
    {
        $this->solution_type = $this->log->solution_type ?? '';
        $this->editingDiagnostic = false;
        $this->diagnosticSaved = false;
    }

    /** @deprecated Kept for backward compat if called via old wire:change */
    public function saveDiagnostic()
    {
        $this->saveDiagnosticConfirmed();
    }

    public function sendEmail()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        try {
            $this->log->load('device.agency');
            
            if (!$this->log->device || !$this->log->device->agency) {
                session()->flash('error', "Impossible d'envoyer un email : pas d'agence associée.");
                $this->closeEmailModal();
                return;
            }

            $service = new ConnectivityIssueService();
            $service->sendAlertEmail($this->log->device->agency, $this->log);

            $this->log->refresh();
            $this->closeEmailModal();
            session()->flash('success', "Email envoyé avec succès pour l'erreur #{$this->log->id}. L'état est maintenant 'Envoyé'.");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi d'email : " . $e->getMessage());
            session()->flash('error', "Erreur de messagerie : Impossible de joindre le serveur d'envoi. Veuillez vérifier votre configuration.");
            $this->closeEmailModal();
        }
    }
}
