<?php

namespace App;

interface SmsInterface
{

    public function send(string $phone, string $text): string;

}
