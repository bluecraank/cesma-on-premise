<?php

namespace App\Mail;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendBackupStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $devices;
    public $modDevices;
    public $totalError;

    /**
     * Create a new message instance.
     */
    public function __construct($modDevices, $totalError)
    {
        $this->devices = $modDevices;
        $this->modDevices = $modDevices;
        $this->totalError = $totalError;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $isSuccess = !$this->totalError ? false : true;

        if($isSuccess) {
            $title = "No errors";
        } else {
            $title = "Errors";
        }

        return new Envelope(
            subject: 'Backup report for ' . Device::count() . ' devices - ' . $title . ' - '. date('d.m.Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.MailTemplateBackup',
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
}
