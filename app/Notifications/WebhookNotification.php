<?php

namespace App\Notifications;

use App\Helpers\Discord\DiscordAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WebhookNotification extends Notification
{

    use Queueable;

    private DiscordAPI $discord;

    private int $new_admin_discord;

    private mixed $data;

    public function __construct($data)
    {
        $this->discord = new DiscordAPI();
        $this->data = (object)$data;
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
        $message = $this->discord->createMessage($this->data->title, $this->data->description, $this->data->fields,
            $this->data->components);

        $data = $this->discord->sendMessage($message, ['type' => 'webhook', 'name' => $this->data->webhook]);

        return [
            'from'       => 'WebhookNotification',
            'sender'     => $data,
            'to_discord' => $this->data->admin_discord,
        ];
    }


    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
