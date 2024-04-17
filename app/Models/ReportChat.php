<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportChat extends Model
{

    use HasFactory;

    protected $fillable = [
        'report_id',
        'messages',
        'created_at',
        'updated_at'
    ];

    protected $table = 'reports_chat';


    public function messages(): Attribute
    {
        return new Attribute(
            get: function ($messages) {
                return json_decode($messages, 1);
            });
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
