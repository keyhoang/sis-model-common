<?php


namespace App\Services;


interface OtpService
{
    public function create(string|int $key);

    public function get(string|int $key);

    public function verify(string|int $key, string|int $otp);

    public function sendOtp(string|int $otp);
}
