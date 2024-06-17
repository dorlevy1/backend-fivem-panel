<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DiscordBot extends Model
{

    use HasFactory;


    protected $guarded = ['id'];
    protected $table = 'discord_bot';


    public function value(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                try {
                    return json_decode($value);
                } catch (\RuntimeException $e) {
                    return json_decode($value);
                }
            });

    }

    public function scopeCategory(
        Builder $query,
                $category
    )
    {
        return $query->where('category', '=', $category);
    }

}
