<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'parent', 'channel_id'];

    const UPDATED_AT = null;
    const CREATED_AT = null;
}
