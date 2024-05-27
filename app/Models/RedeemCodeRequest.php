<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemCodeRequest extends Model
{

    use HasFactory;

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


    public function redeem_code()
    {
        return $this->belongsTo(RedeemCode::class, 'id', 'redeem_request');
    }
}
