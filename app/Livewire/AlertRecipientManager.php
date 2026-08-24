<?php

namespace App\Livewire;

use App\Models\AlertRecipient;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AlertRecipientManager extends Component
{
    public $name = '';
    public $email = '';
    public $editingId = null;

    protected $rules = [
        'name' => 'required|min:2',
        'email' => 'required|email|unique:alert_recipients,email',
    ];

    public function addRecipient()
    {
        $this->validate();

        AlertRecipient::create([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->reset(['name', 'email']);
        session()->flash('message', 'Destinataire ajouté avec succès.');
    }

    public function toggleStatus($id)
    {
        $recipient = AlertRecipient::find($id);
        $recipient->is_active = !$recipient->is_active;
        $recipient->save();
    }

    public function deleteRecipient($id)
    {
        AlertRecipient::destroy($id);
        session()->flash('message', 'Destinataire supprimé.');
    }

    public function sendTestEmail($id)
    {
        $recipient = AlertRecipient::find($id);
        if (!$recipient) return;

        try {
            // Tentative d'envoi de l'email
            Mail::raw("Ceci est un test de connectivité depuis NetDevice Pro.", function ($message) use ($recipient) {
                $message->to($recipient->email)
                    ->subject("Test de configuration Email - NetDevice");
            });

            session()->flash('success', "Email de test envoyé avec succès à {$recipient->email}.");
        } catch (\Exception $e) {
            Log::error("Échec de l'envoi de l'email de test à {$recipient->email}: " . $e->getMessage());
            session()->flash('error', "Impossible d'envoyer l'email de test. Vérifiez la configuration SMTP. L'erreur complète est dans les logs.");
        }
    }

    public function render()
    {
        return view('livewire.alert-recipient-manager', [
            'recipients' => AlertRecipient::orderBy('name')->get()
        ]);
    }
}
