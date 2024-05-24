<?php

namespace App\Enums;

enum Interaction: string
{

    case ADD_BAN = 'add_ban';
    case CANCEL_BAN = "cancel_ban";
    case ADD_KICK = 'add_kick';
    case ADD_WARN = 'add_warn';
    case ADD_ANNOUNCE = 'add_announce';
    case GANG_REQUEST = 'gang_request';
    case CHECK_UPDATE_ROLES = 'check_update_roles';
    case APPROVE_GANG = 'approve_gang';
    case DECLINE_GANG = 'decline_gang';
}