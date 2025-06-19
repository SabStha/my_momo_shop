<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CashDrawerSessionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $session;
    public $eventType;
    public $summary;

    /**
     * Create a new message instance.
     */
    public function __construct($session, $eventType, $summary = null)
    {
        $this->session = $session;
        $this->eventType = $eventType; // 'opened' or 'closed'
        $this->summary = $summary;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->eventType === 'opened'
            ? 'Cash Drawer Opened Notification'
            : 'Cash Drawer Closed Notification';
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
            markdown: 'emails.cash_drawer.session_notification',
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

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->envelope()->subject)
            ->markdown('emails.cash_drawer.session_notification');
    }
}
