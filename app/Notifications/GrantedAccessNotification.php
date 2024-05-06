<?php

namespace App\Notifications;

use App\Helpers\Discord\DiscordAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GrantedAccessNotification extends Notification
{

    use Queueable;

    private DiscordAPI $discord;

    private int $admin_discord;

    public function __construct($admin_discord)
    {
        $this->discord = new DiscordAPI();
        $this->admin_discord = $admin_discord;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['discord'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toDiscord($notifiable)
    {

        $message = $this->discord->createMessage("Permissions", "You've Granted Permissions.");


        $this->discord->sendMessage($message,  ['type' => 'user', 'id' => $this->admin_discord]);

        return [
            'from'       => 'GrantedAccessNotification',
            'sender'     => $notifiable,
            'to_discord' => $this->admin_discord,
            'message'    => $message
        ];
    }


    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
