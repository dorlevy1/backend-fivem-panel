<?php

namespace App\Helpers\Discord\Features;

use App\Helpers\API;
use App\Helpers\Discord\Client;
use App\Helpers\Discord\Discord;
use App\Helpers\Discord\DiscordBot;
use App\Helpers\Discord\DiscordMessage;
use App\Models\Webhook;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord as DiscordPHP;
use Discord\Interaction;
use Discord\Parts\Channel\Channel;


class CreateGangButton
{

    public API $api;
    public DiscordMessage $message;
    public DiscordPHP $discord;
    public DiscordPHP $client;

    public function __construct(DiscordPHP $discord, DiscordPHP $client)
    {
        $this->api = new API();
        $this->discord = $discord;
        $this->client = $client;
        $this->message = new DiscordMessage($client);
        $this->createGangButtonChannel();
    }


    public function createGangButtonChannel(): bool|string
    {
        try {
            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
            if (array_key_exists(Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id,
                $guild->channels->toArray())) {
                return false;
            }
            $group = $guild->channels->create([
                'name' => 'Gang Create Area',
                'type' => Channel::TYPE_GUILD_CATEGORY,
            ]);

            $guild->channels->save($group)->then(function (Channel $channel) use ($guild) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name'       => $channel->name,
                    'channel_id' => $channel->id,
                    'parent'     => true
                ]);

                $channel = $guild->channels->create([
                    'name'      => 'Join-To-Gang',
                    'type'      => Channel::TYPE_PRIVATE_THREAD,
                    'parent_id' => $channel->id,
                    'nsfw'      => false
                ]);
                $guild->channels->save($channel)->then(function (Channel $channel) {
                    Webhook::updateOrCreate([
                        'name' => $channel->name
                    ], [
                        'name'       => $channel->name,
                        'channel_id' => $channel->id,
                        'parent'     => true
                    ]);
                    $embed = $this->message->embed($this->client, [], 'Gang Creation Area');
                    $builder = MessageBuilder::new();
                    $ar = ActionRow::new();
                    $submit = Button::new(Button::STYLE_PRIMARY,
                        'gang_request')->setLabel('Click To Apply Gang Request.');
                    $ar->addComponent($submit);
                    $builder->addEmbed($embed);
                    $builder->addComponent($ar);
                    $channel->sendMessage($builder);

                })->done();
            })->done();

            return true;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

}