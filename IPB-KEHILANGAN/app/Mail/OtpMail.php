<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use SerializesModels;

    public string $otp;
    public string $userName;
    public string $purpose;
    public string $purposeText;

    public function __construct(string $otp, string $userName, string $purpose = 'register')
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->purpose = $purpose;
        $this->purposeText = match ($purpose) {
            'login' => 'login ke akun kamu',
            'reset_password' => 'reset password akun kamu',
            default => 'verifikasi akun kamu',
        };
    }

    public function build()
    {
        return $this->subject('Kode OTP - IPB Kehilangan')
                    ->view('emails.otp');
    }
}
