<?php

namespace App\Helpers\Discord\Features;

use App\Feature;
use App\Helpers\API;
use App\Helpers\Discord\DiscordMessage;
use App\Models\Webhook;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord as DiscordPHP;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;


class RedeemCode implements Feature
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
        $this->handle();
    }


    public function createButtonChannel(Guild $guild): bool|string
    {

        try {

            $embed = $this->message->embed($this->client, [], '');
            $builder = MessageBuilder::new();
            $ar = ActionRow::new();
            $submit = Button::new(Button::STYLE_PRIMARY,
                'create_redeem_code')->setLabel('Create Redeem Code');
            $ar->addComponent($submit);
            $builder->addEmbed($embed);
            $builder->addComponent($ar);
            $guild->channels->get('name', 'create-redeem-code')->sendMessage($builder);

            return true;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

    public function createMainChannel(Guild $guild): void
    {

        if ( !is_null($guild->channels->get('name', 'create-redeem-code'))) {
            return;
        }

        $category = Webhook::where('name', '=', 'Redeem Code Area')->first()->channel_id;


        $channel = $guild->channels->create([
            'name'      => 'create-redeem-code',
            'type'      => Channel::TYPE_GUILD_TEXT,
            'parent_id' => $category,
            'nsfw'      => false
        ]);


        $guild->channels->save($channel)->then(function (Channel $channel) use ($guild) {

            Webhook::updateOrCreate([
                'name' => $channel->name
            ], [
                'name'       => $channel->name,
                'channel_id' => $channel->id,
                'parent'     => false
            ]);

            $this->createButtonChannel($guild);

            return $channel;
        })->done();

    }

    public function handle(): void
    {

        $this->createCat();
        $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
        $this->createMainChannel($guild);
        $this->createLogPage($guild);

    }

    public function createCat(): Guild|string
    {
        try {
            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
            if ( !is_null($guild->channels->get('name', 'Redeem Code Area'))) {
                return false;
            }
            $group = $guild->channels->create([
                'name' => 'Redeem Code Area',
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

                return $guild;
            })->done();

            return $guild;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }

    public function createLogPage(Guild $guild): void
    {
        try {
            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD_LOGS'));

            if ( !is_null($guild->channels->get('name', 'redeem-codes'))) {
                return;
            }

            $category = Webhook::where('name', '=', 'DLPanel')->first()->channel_id;


            $channel = $guild->channels->create([
                'name'      => 'redeem-codes',
                'type'      => Channel::TYPE_GUILD_TEXT,
                'parent_id' => $category,
                'nsfw'      => false
            ]);


            $guild->channels->save($channel)->then(function (Channel $channel) use ($guild) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name'       => $channel->name,
                    'channel_id' => $channel->id,
                    'parent'     => false
                ]);
            })->done();

            return;
        } catch (\ErrorException $e) {
            return;
        }
    }

}