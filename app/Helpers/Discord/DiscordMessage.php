<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Models\Webhook;
use App\Notifications\WebhookNotification;
use PhpParser\Node\Expr\Cast\Object_;

#[AllowDynamicProperties] class DiscordMessage
{

    public function __construct()
    {
        $this->discordAPI = new DiscordAPI();
    }


    public function createButtonComponent($buttons): array
    {
        $data = [];
        foreach ($buttons as $key => $button) {
            $data[$key]['type'] = 1;
            $data[$key]['components'][] = $button;
        }

        return $data;
    }

    public function message($data): bool
    {
        $data = (object)$data;
        auth()->attempt(['discord_id' => $data->adminDiscordId]);
        $this->createMessage([
            'adminDiscordId' => $data->adminDiscordId,
            'title'          => "Removed Ban",
            'description'    => "<@{$data->adminDiscordId}> Removed Ban For <@{$data->playerDiscordId}>",
            'webhook'        => "bans",
            'fields'         => $data->fields,
        ]);

        return true;
    }

    public function createMessage($data)
    {
        $data = (object)$data;
        auth()->attempt(['discord_id' => $data->adminDiscordId]);


        $details = [
            'admin_discord' => $data->adminDiscordId,
            'title'         => $data->title,
            'description'   => $data->description,
            'fields'        => $data->fields ?? [],
            'components'    => $data->components ?? [],
        ];

        if ( !empty($data->reply)) {
            $details['reply'] = [
                'message_id' => $data->reply,
                'guild_id'   => env('DISCORD_BOT_GUILD_LOGS'),
                'channel_id' => Webhook::where('name', '=', 'bans')->first()->channel_id
            ];
        }

        $message = $this->createDraft($details);

        (new Webhook())->notify(new WebhookNotification([
            'admin_discord' => $data->adminDiscordId,
            'message'       => $message,
            'webhook'       => $data->webhook
        ]));

        return true;
    }

    public function createDraft($data): array
    {

        $data = (object)$data;

        $details = [
            "embeds" => [
                [
                    "title"       => $data->title,
                    "author"      => [
                        "name"     => "DLPanel",
                        "icon_url" => "https://cdn.discordapp.com/attachments/1236147966390046732/1236387837394288830/Screenshot_2024-05-04_at_21.43.40.png?ex=6637d367&is=663681e7&hm=7549a2544ea9a978a062b984f9889235a203b59b3ec8ece64840148762a05425&",
                        "url"      => "https://discord.js.org"
                    ],
                    "thumbnail"   => [
                        "url" => "https://cdn.discordapp.com/attachments/1236147966390046732/1236387576659841235/image.png?ex=6637d329&is=663681a9&hm=c2abe6e118a47548571b3f9110ee40f406995d087577ac07e6e7368c1736fe0d&"
                    ],
                    "description" => $data->description,
                    "timestamp"   => date("c"),
                    "footer"      => [
                        "text" => "DLPanel By D.D.L"
                    ],
                    "fields"      => $data->fields ?? []
                ]
            ],
            ...$data->components ?? [],
        ];

        if ( !empty($data->reply)) {
            $details['message_reference'] = $data->reply;
        }

        return $details;
    }


}