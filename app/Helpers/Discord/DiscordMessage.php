<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Models\GangCreationRequest;
use App\Models\Webhook;
use App\Notifications\WebhookNotification;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction as In;

#[AllowDynamicProperties] class DiscordMessage
{

    public function __construct(Discord $client)
    {
        $this->client = $client;
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
        $this->createMessage([
            'adminDiscordId' => $data->adminDiscordId,
            'title'          => "Removed Ban",
            'description'    => "<@{$data->adminDiscordId}> Removed Ban For <@{$data->playerDiscordId}>",
            'webhook'        => "bans",
            'fields'         => $data->fields,
            'components'     => $data->components ?? [],
            'ephemeral'      => $data->ephemeral ?? false
        ]);

        return true;
    }

    public function createMessage($data)
    {
        $data = (object)$data;

        $details = [
            'admin_discord' => $data->adminDiscordId,
            'title'         => $data->title,
            'description'   => $data->description,
            'fields'        => $data->fields ?? [],
            'components'    => $data->components ?? [],
            'ephemeral'     => $data->ephemeral ?? false
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
            'id'            => $data->id ?? null,
            'webhook'       => $data->webhook
        ]));

        return true;
    }


    public function createSummaryRequestEmbed(
        Discord $client,
        In $interaction,
        $text,
        $readyForRequest,
        $talkTo
    ): \Discord\Parts\Part|\Discord\Repository\AbstractRepository {
        $fields = [
            [
                'name'  => 'Chosen Gang',
                'value' => GangCreationRequest::where('discord_id', '=', $interaction->user->id)->first()->gang_name
            ],
            ['name' => 'Members', 'value' => $text],
        ];
        $channelId = Webhook::where('name', '=', 'join-to-gang')->first()->channel_id;
        !$readyForRequest && $fields[] = [
            'name'  => 'Please Notice',
            'value' => "you have one or more members that\ndoes not have Allowlist Role.\nTalk to {$talkTo} soon as possible and update the Request\n\nAfter those members get the Allowlist Role.\nClick on the button inside <#{$channelId}>"
        ];

        return $this->embed($client, $fields, 'Gang Request');
    }

    public function embed(Discord $discord, $fields, $title)
    {

        $embed = $discord->factory(Embed::class);
        if ( !empty($fields)) {
            $embed->addField(...$fields);
        }
        $embed->setTitle($title)
              ->setAuthor('DLPanel',
                  'https://cdn.discordapp.com/attachments/1236147966390046732/1236387837394288830/Screenshot_2024-05-04_at_21.43.40.png?ex=6637d367&is=663681e7&hm=7549a2544ea9a978a062b984f9889235a203b59b3ec8ece64840148762a05425&',
                  'https://discord.js.org')
              ->setThumbnail('https://cdn.discordapp.com/attachments/1236147966390046732/1236387576659841235/image.png?ex=6637d329&is=663681a9&hm=c2abe6e118a47548571b3f9110ee40f406995d087577ac07e6e7368c1736fe0d&')
              ->setFooter('DLPanel By D.D.L')->setTimestamp();

        return $embed;
    }

    protected function button(MessageBuilder $builder,$customId,$label): MessageBuilder
    {
        $action = ActionRow::new();
        $button = Button::new(Button::STYLE_PRIMARY)->setCustomId($customId);
        $button->setLabel($label);
        $action->addComponent($button);
        $builder->addComponent($action);

        return $builder;
    }

    public function createDraft($data): array
    {

        $data = (object)$data;

        $details = [
            "embeds" => [
                [
                    "color"       => hexdec("FFFFFF"),
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

    protected function messageSummaryRequest(In $interaction)
    {
        $request = GangCreationRequest::where('discord_id', '=', $interaction->user->id)->first();

        $roleExists = false;
        $talkTo = '';
        $text = '';
        $rolesBoss = $interaction->guild->members->get('id', $request->boss)->roles;
        $rolesCo = $interaction->guild->members->get('id', $request->co_boss)->roles;
        $exists = array_key_exists(1192227507508871349, $rolesBoss->toArray()) ? ' ' . '✅' : ' ' . '❌';
        $existsCo = array_key_exists(1192227507508871349, $rolesCo->toArray()) ? ' ' . '✅' : ' ' . '❌';

        $text .= "Boss -  <@{$request->boss}> {$exists}\n\n";
        $text .= "Co Boss -  <@{$request->co_boss}> {$existsCo}\n\n";

        foreach (explode(',', $request->members) as $key => $member) {
            $roles = $interaction->guild->members->get('id', $member)->roles;
            if (array_key_exists(1192227507508871349, $roles->toArray())) {
                $roleExists = true;
            } else {
                $roleExists = false;
                $talkTo .= "<@{$member}> ";
            }
            $exists = $roleExists ? ' ' . '✅' : ' ' . '❌';
            $key = $key + 1;
            $text .= "Member No.{$key} -  <@{$member}> {$exists}\n\n";
        }
        $embed = $this->createSummaryRequestEmbed($this->client, $interaction, $text, $roleExists, $talkTo);


        return MessageBuilder::new()->addEmbed($embed);
    }

}