<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InactiveAgenciesSummary extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $agencies;
    public $eventTime;

    /**
     * Create a new message instance.
     */
    public function __construct($agencies)
    {
        $this->agencies = $agencies;
        $this->eventTime = now()->format('d/m/Y H:i:s');
        $this->queue = 'scan';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $count = count($this->agencies);
        return new Envelope(
            subject: "🚨 ALERTE MULTIPLE : {$count} Agence(s) Hors-ligne Détectée(s)",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inactive-agencies-summary',
        );
    }
}
