<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{

    use HasFactory;

    protected $connection = 'second_db';
    protected $fillable = ['discord', 'license', 'expire', 'ip', 'bannedby', 'name', 'reason'];
    const UPDATED_AT = null;
    const CREATED_AT = null;

    public function player(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Player::class, 'citizenid', 'citizenid');
    }
}
