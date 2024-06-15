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
    case SET_GANG_NAME = 'set_gang_name';
    case REDEEM_CODE = 'create_redeem_code';
    case REDEEM_INSERT_VEHICLES = 'redeem_insert_vehicles';
    case REDEEM_INSERT_CASH = 'redeem_insert_cash';
    case REDEEM_INSERT_ITEMS = 'redeem_insert_items';
    case REDEEM_INSERT_WEAPONS = 'redeem_insert_weapons';
    case UPDATE_REDEEM = 'update_redeem';
    case DELETE_REDEEM = 'delete_redeem';
    case DONE_REDEEM = 'done_redeem';
    case UPDATE_FIRST_TIME = 'update_first_time';
}
