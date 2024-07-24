<?php


namespace App\Helpers\Discord;

use Illuminate\Support\Facades\Facade;

class Client extends Facade
{

    protected static function getFacadeAccessor()
    {
        return ClientService::class;
    }
}
