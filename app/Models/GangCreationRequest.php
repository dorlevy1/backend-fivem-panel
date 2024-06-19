<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GangCreationRequest extends Model
{

    use HasFactory;

    protected $connection = 'second_db';
    protected $table = 'gang_creation_request';


    protected $guarded = ['id'];


    public function scopeReadyForApprove(Builder $query)
    {
        return $query->where('ready_for_approve', '=', 1);
    }
}
