<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;

class AllOfflineAgenciesAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $offlineAgencies;
    public $eventTime;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $offlineAgencies)
    {
        $this->offlineAgencies = $offlineAgencies;
        $this->eventTime = now()->format('d/m/Y H:i:s');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Internet Rétabli - État Actuel des Agences Déconnectées',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.all-offline-agencies-alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
