<?php

namespace App\Mail;

use App\Models\StockReturn;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StockReturn $return) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi Return #' . $this->return->return_number . ' — Kasyraa.co',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.return-notification');
    }
}
