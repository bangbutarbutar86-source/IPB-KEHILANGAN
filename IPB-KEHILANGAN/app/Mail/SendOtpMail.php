<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName; 

    // Menangkap data OTP dan Nama User yang dikirim dari Controller
    public function __construct($otp, $userName) // <-- Tambahkan $userName di sini
    {
        $this->otp = $otp;
        $this->userName = $userName; // <-- Tambahkan baris ini
    }

    public function build()
    {
        return $this->subject('Kode OTP Verifikasi Akun IPB Kehilangan')
                    ->view('emails.otp'); 
    }
}