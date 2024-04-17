<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{

    use HasFactory;

    protected $fillable = [
        'disocrd_id',
        'citizen_id',
        'title',
        'description',
        'claim_by',
        'status',
        'created_at',
        'updated_at'
    ];


    public function claimBy(): Attribute
    {
        return new Attribute(
            get: function ($claimBy) {
                return Admin::where('discord_id', '=', $claimBy)->first();
            });
    }

    public function report_chat()
    {
        return $this->hasOne(ReportChat::class);
    }
}
