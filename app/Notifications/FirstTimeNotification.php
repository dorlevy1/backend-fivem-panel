<?php

namespace App\Notifications;

use App\Helpers\Discord\DiscordAPI;
use App\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FirstTimeNotification extends Notification
{

    use Queueable;

    private DiscordAPI $discord;

    private int $new_admin_discord;

    public function __construct($new_admin_discord)
    {
        $this->discord = new DiscordAPI();
        $this->new_admin_discord = $new_admin_discord;
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
        $fields = [
            [
                'name' => 'Please Follow the instructions.',
                'value' => ''
            ],
            [
                'name' => '1. Click the Access Button:',
                'value' => 'Upon clicking the designated access button, you will be redirected to the panel login page.'
            ],
            [
                'name' => '2. Discord Authentication:',
                'value' => 'To gain access, you must authenticate using your Discord account. Click the "Login with Discord" button and follow the prompts to log in securely.'
            ],
            [
                'name' => '3. Discord Authorization:',
                'value' => 'After logging in, you\'ll be asked to authorize access to the panel from your Discord account. Confirm the authorization to proceed.'
            ],
            [
                'name' => '4. Wait for Approval:',
                'value' => 'Once authorized, your access request will be sent to the owners/administrators for approval. Please be patient as they review and approve your request.'
            ],
            [
                'name' => '5. Notification of Approval:',
                'value' => 'You will receive a notification via Discord once your access request has been approved. This notification will include instructions on how to proceed.'
            ],
            [
                'name' => '6. Access Granted:',
                'value' => 'Upon approval, you will gain access to the panel and its functionalities. You can now proceed to use the panel as needed.'
            ],
            [
                'name' => 'Note:',
                'value' => 'Access to the panel is exclusively granted through Discord authentication. Ensure you are logged in to Discord when accessing the panel, as no alternative login methods are available.'
            ]
        ];

        $components['components'] = [
            [
                "type" => 1,
                "components" => [
                    [
                        "type" => 2,
                        "label" => "Start Authorization.",
                        "style" => 5,
                        "url" => "https://google.com",
                        //                            "custom_id" => "invitation"
                    ]
                ]
            ]
        ];

        $invitation = Message::createDraft(['title' => "Invitation For DLPanel",
            'description' => "You've got an invitation!",
            'fields' => $fields,
            'components' => $components]);

        $this->discord->sendMessage($invitation, ['type' => 'user', 'id' => $this->new_admin_discord]);

        return [
            'from' => 'FirstTimeNotification',
            'sender' => $notifiable,
            'to_discord' => $this->new_admin_discord,
        ];
    }


    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
