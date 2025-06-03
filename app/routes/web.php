<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendResetOtpMail;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-mail', function () {
    Mail::to('sokmasterlychanon06@gmail.com')->send(new SendResetOtpMail(123456));
    return 'Mail sent!';
});
