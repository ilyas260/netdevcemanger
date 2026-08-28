<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeviceStatusAlert extends Mailable
{
    use SerializesModels;

    public $targetName;
    public $ipAddress;
    public $status;
    public $type; // 'Appareil' ou 'Agence'
    public $alertMessage; // Message d'alerte personnalisé (optionnel)
    public $eventTime;

    /**
     * Create a new message instance.
     */
    public function __construct($targetName, $ipAddress, $status, $type = 'Appareil', $alertMessage = null)
    {
        $this->targetName = $targetName;
        $this->ipAddress = $ipAddress;
        $this->status = $status;
        $this->type = $type;
        $this->alertMessage = $alertMessage;
        $this->eventTime = now()->format('d/m/Y H:i:s');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ($this->status === 'offline') 
            ? "🚨 ALERTE : {$this->type} Hors-ligne ({$this->targetName})"
            : "✅ RÉTABLISSEMENT : {$this->type} En ligne ({$this->targetName})";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.device-status-alert',
        );
    }
}
