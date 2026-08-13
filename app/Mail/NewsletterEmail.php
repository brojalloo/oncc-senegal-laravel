<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Infolettre rédigée par un administrateur.
 *
 * Volontairement sans ShouldQueue : l'envoi a déjà lieu dans le travail
 * SendNewsletter, lui-même en file d'attente. Remettre chaque message en file
 * depuis ce travail n'ajouterait qu'un aller-retour.
 */
class NewsletterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $subjectLine,
        public readonly string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->body);
    }
}
