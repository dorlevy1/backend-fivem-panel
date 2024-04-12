<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\StringifyScope;

class Player extends Model
{

    use HasFactory;

    protected $connection = 'second_db';


    protected static function boot(): void
    {
        parent::boot();

        //Any time this model is used, it will implement the StringifyGuidRule
        static::addGlobalScope(new StringifyScope());
    }

    public function inventory(): Attribute
    {
        return new Attribute(
            get: function ($inventory) {
                return json_decode($inventory);
            });
    }


    public function job(): Attribute
    {
        return new Attribute(
            get: function ($job) {
                return json_decode($job);
            });
    }

    public function metadata(): Attribute
    {
        return new Attribute(
            get: function ($metadata) {
                return json_decode($metadata);
            });
    }

    public function gang(): Attribute
    {
        return new Attribute(
            get: function ($gang) {
                return json_decode($gang);
            });
    }

    public function skillsinfo(): Attribute
    {
        return new Attribute(
            get: function ($skillsinfo) {
                return json_decode($skillsinfo);
            });
    }

    public function money(): Attribute
    {
        return new Attribute(
            get: function ($money) {
                return json_decode($money);
            });
    }

    public function charinfo(): Attribute
    {
        return new Attribute(
            get: function ($charinfo) {
                return json_decode($charinfo);
            });
    }

    public function ban(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ban::class, 'citizenid');
    }

}
