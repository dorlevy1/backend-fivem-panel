<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('playerWarns.{userId}', function ($user, $userId) {
    return [$user, $userId];

    return true;
});