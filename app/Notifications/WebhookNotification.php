<?php

namespace App\Notifications;

use App\Helpers\Discord\DiscordAPI;
use App\Helpers\Discord\DiscordWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WebhookNotification extends Notification
{

    use Queueable;

    private DiscordAPI $discord;

    private int $admin_discord;

    private mixed $data;
    private array $message;
    private string $webhook;

    public function __construct($data)
    {
        $this->discord = new DiscordAPI();

        $this->admin_discord = $data['admin_discord'];
        $this->message = $data['message'];
        $this->webhook = $data['webhook'];
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

        $data = (new DiscordWebhook($this->webhook, $this->message))->send();

        return [
            'from'       => 'WebhookNotification',
            'sender'     => $data,
            'to_discord' => $this->admin_discord,
        ];
    }


    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
