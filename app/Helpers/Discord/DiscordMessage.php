<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Message;
use App\Models\GangCreationRequest;
use App\Models\Player;
use App\Models\Webhook;
use App\Notifications\WebhookNotification;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction as In;

#[AllowDynamicProperties] class DiscordMessage extends Message
{

    public function __construct(Discord $client)
    {
        parent::__construct();
        $this->client = $client;
    }



    public function createButtons($buttons, $cb = null)
    {
        $ar = ActionRow::new();

        foreach ($buttons as $button) {
            $button = (object)$button;
            $btn = Button::new($button->style)->setCustomId($button->custom_id);
            $btn->setLabel($button->label);
            $ar->addComponent($btn);

            !is_null($cb) && $cb($btn);
        }

        return $ar;
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

    public static function message($data): bool
    {
        $data = (object)$data;
        self::createMessage([
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

    protected function button(MessageBuilder $builder, $customId, $label): MessageBuilder
    {
        $action = ActionRow::new();
        $button = Button::new(Button::STYLE_PRIMARY)->setCustomId($customId);
        $button->setLabel($label);
        $action->addComponent($button);
        $builder->addComponent($action);

        return $builder;
    }



}
