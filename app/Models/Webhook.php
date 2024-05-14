<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Webhook extends Model
{

    use HasFactory, Notifiable;

    protected $fillable = ['name', 'parent', 'channel_id'];

    const UPDATED_AT = null;
    const CREATED_AT = null;
}
