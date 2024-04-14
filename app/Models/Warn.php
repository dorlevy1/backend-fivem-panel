<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warn extends Model
{

    use HasFactory;

    protected $fillable = ['discord', 'license', 'name', 'reason', 'warned_by', 'created_at', 'updated_at'];

}
