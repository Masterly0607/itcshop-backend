<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCustomerOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $otp;

    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

 public function handle()
{
    $response = Http::withToken(env('RESEND_API_KEY'))
        ->post('https://api.resend.com/emails', [
            "from" => "onboarding@resend.dev",
            "to" => $this->email,
            "subject" => "Your OTP Code",
            "html" => "<p>Your OTP is: <strong>{$this->otp}</strong></p>",
        ]);

    if (!$response->successful()) {
        logger()->error('❌ Resend send failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    } else {
        logger()->info('✅ Resend send success', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }
}

}
