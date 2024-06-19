<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemCodeRequestHistory extends Model
{

    use HasFactory;

    protected $table = 'redeem_codes_history';
    protected $connection = 'second_db';

    protected $guarded = ['id'];

}
