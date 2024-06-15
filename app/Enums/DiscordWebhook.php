<?php

namespace App\Enums;


enum DiscordWebhook: string
{

    case BAN = 'bans';
    case KICKS = 'kicks';
    case WARNS = 'warns';
    case ANNOUNCE = 'announcements';
    case REDEEM = 'redeem-codes';
    case PERMISSION = 'permissions';
    case GANG_CREATION = 'gang-creation';
    case PRIVATE_USER = 'private';

}