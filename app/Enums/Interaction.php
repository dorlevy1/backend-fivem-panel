<?php

namespace App\Enums;

enum Interaction: string
{

    case ADD_BAN = 'add_ban';
    case CANCEL_BAN = "cancel_ban";
    case ADD_KICK = 'add_kick';
    case ADD_WARN = 'add_warn';
    case ADD_ANNOUNCE = 'add_announce';
}