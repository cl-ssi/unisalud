<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Epi\SuspectCase;

class DelegateChagasNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $suspectCase;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SuspectCase $suspectCase)
    {
        //
        $this->suspectCase = $suspectCase;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Solicitud N° {$this->suspectCase->id} de Chagas";
        return $this->view('epi.chagas.mail.delegatenotification')->subject($subject);
    }
}
