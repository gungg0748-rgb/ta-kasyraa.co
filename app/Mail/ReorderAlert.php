<?php

namespace App\Mail;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReorderAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ProductVariant $variant) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[STOK KRITIS] ' . $this->variant->product->name . ' — Kasyraa.co',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reorder-alert');
    }
}
