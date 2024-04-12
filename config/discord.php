<?php

return [

    'client_id'     => env('DISCORD_CLIENT_ID'),
    'client_secret' => env('DISCORD_CLIENT_SECRET'),
    'prefix'        => env('DISCORD_PREFIX'),
    'grand_type'    => env('DISCORD_GRAND_TYPE'),
    'redirect_uri'  => "http://dont-know.test/api/auth",
    'scopes'        => env('DISCORD_SCOPES')
];
