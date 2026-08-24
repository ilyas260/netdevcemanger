<?php

namespace App\Mail;

use App\Models\ErrorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IssueResolutionNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $agencyName;
    public $ipAddress;
    public $errorType;
    public $errorMessage;
    public $resolutionNote;
    public $resolvedBy;
    public $eventTime;
    public $solutionType;
    public $solutionLabel;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $agencyName,
        string $ipAddress,
        string $errorType,
        string $errorMessage,
        string $resolutionNote,
        string $resolvedBy,
        string $solutionType = '',
        string $solutionLabel = ''
    ) {
        $this->agencyName = $agencyName;
        $this->ipAddress = $ipAddress;
        $this->errorType = $errorType;
        $this->errorMessage = $errorMessage;
        $this->resolutionNote = $resolutionNote;
        $this->resolvedBy = $resolvedBy;
        $this->eventTime = now()->format('d/m/Y H:i:s');
        $this->queue = 'scan';
        $this->solutionType = $solutionType;
        $this->solutionLabel = $solutionLabel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Résolution : Problème corrigé pour {$this->agencyName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.issue-resolution-notification',
        );
    }
}
