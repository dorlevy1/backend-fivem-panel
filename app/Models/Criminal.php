<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criminal extends Model
{

    use HasFactory;

    protected $connection = 'second_db';


    public function gang()
    {
        return $this->belongsTo(Gang::class, 'name', 'organization');
    }
}
