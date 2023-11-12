<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode; // Add a public property to hold the verification code.

    /**
     * Create a new message instance.
     *
     * @param string $verificationCode
     */
    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('taguigconnect@gmail.com', 'System')
            ->subject('Verification Email')
            ->view('emails.verify') // Specify the email view template.
            ->with(['verificationCode' => $this->verificationCode]); // Pass the verification code to the view.
    }
}