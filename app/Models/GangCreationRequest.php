<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GangCreationRequest extends Model
{

    use HasFactory;

    protected $connection = 'second_db';
    protected $table = 'gang_creation_request';


    protected $fillable = ['discord_id', 'gang_name', 'boss', 'co_boss', 'members', 'ready_for_approve', 'channel_id'];
}
