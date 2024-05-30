<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{

    use HasFactory;

    protected $fillable = ['code', 'redeem_request'];

    protected $primaryKey = 'redeem_request';


    public function request()
    {
        return $this->hasOne(RedeemCodeRequest::class, 'id');
    }
}
