<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    use HasFactory;

    protected $guarded = ['id'];


    public function value(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                return json_decode($value) ?? $value;
            });
    }

    public function scopeCategory(Builder $query, $category)
    {
        return $query->where('category', '=', $category);
    }

}
