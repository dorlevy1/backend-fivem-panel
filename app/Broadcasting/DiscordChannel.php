<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;

class DiscordChannel
{

    /**
     * Create a new channel instance.
     */



    public function __construct()
    {
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function send($notifiable, Notification $notification)
    {

        if (method_exists($notifiable, 'discordNotification')) {
            $id = $notifiable->discordNotification($notifiable);
        } else {
            $id = $notifiable->getKey();
        }

        $data = method_exists($notification, 'toDiscord')
            ? $notification->toDiscord($notifiable)
            : $notification->toArray($notifiable);

        if (empty($data)) {
            return false;
        }
        app('log')->info(json_encode([
            'id'   => $id,
            'data' => $data,
        ]));

        return true;
    }
}
