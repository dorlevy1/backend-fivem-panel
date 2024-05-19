<?php

namespace App\Enums;


enum DiscordWebhook: string
{

    case BAN = 'bans';
    case KICKS = 'kicks';
    case WARNS = 'warns';
    case ANNOUNCE = 'announce';
    case REDEEM = 'redeem-code';
    case GANG_CREATION = 'gang-creation';
    case PRIVATE_USER = 'private';

}