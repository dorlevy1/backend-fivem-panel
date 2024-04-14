<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{

    use HasFactory;

    protected $table = 'permissions';

    protected $fillable = [
        'discord_id',
        'scopes',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'permissions' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'discord_id', 'discord_id');
    }

    public function pending()
    {
        return $this->belongsTo(PendingPermission::class, 'discord_id', 'discord_id');
    }
}
