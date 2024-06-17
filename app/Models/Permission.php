<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Permission extends Model
{

    use HasFactory, Notifiable;

    protected $table = 'admin_permissions';

    protected $guarded = ['id'];
    protected $casts = [
        'permissions' => 'array',
    ];


    public function discordNotification($notifiable)
    {

        return 'identifier-from-notification-for-log: ' . $this->id;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'discord_id', 'discord_id');
    }

    public function pending()
    {
        return $this->belongsTo(PendingPermission::class, 'discord_id', 'discord_id');
    }

}
