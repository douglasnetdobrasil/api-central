<?php

namespace App\Mail;

use App\Models\ContaAReceber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment; // Importar
use Illuminate\Queue\SerializesModels;

class CobrancaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $conta;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(ContaAReceber $conta, string $pdfContent)
    {
        $this->conta = $conta;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cobrança Referente a Fatura #' . ($this->conta->venda->id ?? $this->conta->id),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cobranca.fatura', // Crie esta view markdown
            with: [
                'contaUrl' => url('/'), // Link para o portal do cliente, se houver
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'fatura_' . ($this->conta->venda->id ?? $this->conta->id) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}