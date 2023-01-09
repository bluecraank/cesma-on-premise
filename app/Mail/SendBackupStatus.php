<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendBackupStatus extends Mailable
{
    use Queueable, SerializesModels;
    public $backups, $devices, $totalError;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($backups, $devices, $totalError)
    {
        $this->backups = $backups;
        $this->devices = $devices;
        $this->totalError = ($totalError) ? "[Erfolgreich]" : "[Fehlgeschlagen]";
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Wöchentlicher Bericht Backups Switche '.$this->totalError,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mail.MailTemplateBackup',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
