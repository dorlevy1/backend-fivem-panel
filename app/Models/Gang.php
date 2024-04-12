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


    public function players()
    {
        return $this->hasMany(Criminal::class, 'organization', 'name');
    }
}
