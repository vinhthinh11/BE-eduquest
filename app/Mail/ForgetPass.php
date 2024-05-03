<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgetPass extends Mailable
{
    use Queueable, SerializesModels;

    public $newPass;

    public function __construct($newPass)
    {
        $this->newPass = $newPass;
    }

    public function build()
    {
        return $this->subject("Cập nhập mật khẩu của bạn!")
                    ->view('', [
                        'newPass'  => $this->newPass
                    ]);
    }
}
