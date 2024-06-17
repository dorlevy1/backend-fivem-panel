<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PendingPermission extends Model
{

    use HasFactory;

    protected $table = 'pending_permissions';
    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
    ];


    public function admin()
    {
        return $this->belongsTo(Admin::class, 'discord_id', 'discord_id');
    }

    public function permissions()
    {
        return $this->hasOne(Permission::class, 'discord_id', 'discord_id');
    }
}
