<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application, string $oldStatus, string $newStatus)
    {
        $this->application = $application;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Status Update',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.application-status-changed',
            with: [
                'jobTitle' => $this->application->jobListing->title,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'companyName' => $this->application->jobListing->company
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}