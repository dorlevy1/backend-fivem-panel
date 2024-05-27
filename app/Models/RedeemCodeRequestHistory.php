<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemCodeRequestHistory extends Model
{

    use HasFactory;

    protected $table = 'redeem_codes_history';
    protected $fillable = [
        'citizenid',
        'request_by',
        'weapons',
        'vehicles',
        'items',
        'cash',
        'discord_id',
        'channel_id'
    ];

}
