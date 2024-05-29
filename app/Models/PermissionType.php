<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionType extends Model
{

    use HasFactory;

    protected $table = 'permission_type';
    protected $guarded = [];
}
