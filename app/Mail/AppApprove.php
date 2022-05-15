<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppApprove extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $appId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $appId)
    {
        //
        $this->email;
        $this->appId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
                ->subject('MSWDO: Your application is approved!')
                ->with(['appId', $this->appId])
                ->markdown('emails.appapprove');
    }
}
