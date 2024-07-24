<?php

namespace App;


class RealSms implements SmsInterface
{
    public function send(string $phone, string $text): string
    {
        // Logic to send a real SMS
        return "Real SMS sent to $phone with message: $text";
    }
}
