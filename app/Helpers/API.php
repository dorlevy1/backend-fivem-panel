<?php


namespace App\Helpers;

use App\Helpers\AccessToken;
use App\Models\User;
use App\Repositories\DiscordRepository;
use Illuminate\Support\Facades\Session;
use PHPUnit\Runner\ErrorException;

class API
{

    public function apiRequest($url, $data, $token = null, $type = null, $json = null, $method = 'POST')
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($method === 'POST') {
            if ( !is_null($data) && is_null($json)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } elseif ( !is_null($data) && $json) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }


            $headers[] = 'Accept: application/json';
            if ( !is_null($token) && is_null($type)) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }
            if ( !is_null($token) && !is_null($type)) {
                $headers[] = 'Authorization: Bot ' . $token;
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        return json_decode($response);
    }
}