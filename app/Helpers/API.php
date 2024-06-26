<?php


namespace App\Helpers;

class API
{

    public function apiRequest($url, $data, $token = null, $type = null, $json = null, $method = 'POST')
    {


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $headers[] = 'Accept: application/json';
        if ( !is_null($token) && is_null($type)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if ( !is_null($token) && !is_null($type)) {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: Bot ' . $token;
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method === 'POST') {
            if ( !is_null($data) && !$json) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } elseif ( !is_null($data) && $json) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        return json_decode($response);
    }

}