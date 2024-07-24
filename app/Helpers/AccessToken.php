<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use PHPUnit\Runner\ErrorException;


class AccessToken
{

    protected $access_token = null;
    protected $refresh_token = null;

    public function __construct($token)
    {
        $token = (object)$token;
        $this->access_token = $token->access_token;
        $this->refresh_token = $token->refresh_token;
    }

    public function getToken()
    {
        return $this->access_token;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function setToken($access_token): void
    {
        $this->access_token = $access_token;
    }

    public function setRefreshToken($refresh_token): void
    {
        $this->refresh_token = $refresh_token;
    }
}
