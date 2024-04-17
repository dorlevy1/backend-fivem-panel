<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('warnsUpdate', function ($user) {
    return ['dor'];

    return true;
});