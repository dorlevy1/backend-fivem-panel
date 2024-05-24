<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criminal extends Model
{

    use HasFactory;

    protected $connection = 'second_db';


    protected $fillable = ['identifier', 'name', 'organization', 'stats'];

    const UPDATED_AT = null;
    const CREATED_AT = null;

    public function gang()
    {
        return $this->belongsTo(Gang::class, 'name', 'organization');
    }
}
