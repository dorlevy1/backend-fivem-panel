<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Gang extends Model
{

    use HasFactory;

    protected $table = 'organizations';
    protected $connection = 'second_db';

    protected $fillable = ['name', 'owner', 'color', 'zones', 'picture'];

    const UPDATED_AT = null;
    const CREATED_AT = null;

    public function criminals()
    {
        return $this->hasMany(Criminal::class, 'organization', 'name');
    }
}
