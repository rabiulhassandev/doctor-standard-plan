<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InboxReceived extends Mailable
{
    public function __construct(public string $kind, public string $reference, public int $recordId) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New practice '.($this->kind === 'appointments' ? 'appointment request' : 'inquiry'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.inbox-received');
    }
}
