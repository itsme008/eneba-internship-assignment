<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderKeys extends Mailable
{
    public function __construct(public array $receipt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your demo keys · '.$this->receipt['number']);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-keys', text: 'mail.order-keys-text', with: ['receipt' => $this->receipt]);
    }
}
