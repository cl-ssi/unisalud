<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Fq\FqRequest;

class NewNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $fqRequest;

    public function __construct(FqRequest $fqRequest)
    {
        $this->fqRequest = $fqRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('fq.mail.newnotification')->subject('Nueva Solicitud Pacientes FQ - Unisalud');
    }
}
