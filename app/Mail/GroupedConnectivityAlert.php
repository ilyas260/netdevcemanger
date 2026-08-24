<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupedConnectivityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $issues;
    public $eventTime;
    public $isResolved;

    public function __construct($issues, $isResolved = false)
    {
        $this->issues = $issues;
        $this->eventTime = now()->format('d/m/Y H:i:s');
        $this->isResolved = $isResolved;
    }

    public function envelope(): Envelope
    {
        $subject = $this->isResolved 
            ? "✅ RÉSOLUTION GROUPEE : Connectivité Rétablie (" . count($this->issues) . " agence(s))"
            : "🚨 ALERTE GROUPEE : Problèmes de Connectivité (" . count($this->issues) . " agence(s))";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.grouped-connectivity-alert',
        );
    }
}
