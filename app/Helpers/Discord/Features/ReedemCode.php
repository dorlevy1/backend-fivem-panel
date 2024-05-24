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
use Discord\Parts\Guild\Guild;


class ReedemCode
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


    public function createGangButtonChannel(Guild $guild): bool|string
    {

        try {
            if ( !is_null($guild->channels->get('name', 'gang-requests'))) {
                return false;
            }

            $embed = $this->message->embed($this->client, [], 'Gang Creation Area');
            $builder = MessageBuilder::new();
            $ar = ActionRow::new();
            $submit = Button::new(Button::STYLE_PRIMARY,
                'gang_request')->setLabel('Click To Apply Gang Request.');
            $ar->addComponent($submit);
            $builder->addEmbed($embed);
            $builder->addComponent($ar);
            $guild->channels->get('name', 'join-to-gang')->sendMessage($builder);

            return true;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

    private function createJoinToGang(Guild $guild): void
    {

        if ( !is_null($guild->channels->get('name', 'join-to-gang'))) {
            return;
        }

        $category = Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id;

        $channel = $guild->channels->create([
            'name'      => 'Join-To-Gang',
            'type'      => Channel::TYPE_GUILD_TEXT,
            'parent_id' => $category,
            'nsfw'      => false
        ]);

        $guild->channels->save($channel)->then(function (Channel $channel) {
            Webhook::updateOrCreate([
                'name' => $channel->name
            ], [
                'name'       => $channel->name,
                'channel_id' => $channel->id,
                'parent'     => false
            ]);

            return $channel;
        })->done();

    }

    public function handle(): void
    {

        $this->createGangCat();
        $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
        $this->createJoinToGang($guild);
        $this->createGangButtonChannel($guild);
        $this->createRequestsPage($guild);

    }

    private function createGangCat(): Guild|string
    {
        try {
            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
            if ( !is_null($guild->channels->get('name', 'Gang Create Area'))) {
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

                return $guild;
            })->done();

            return $guild;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }

    private function createRequestsPage(Guild $guild): void
    {
        try {
            if ( !is_null($guild->channels->get('name', 'gang-requests'))) {
                return;
            }

            $category = Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id;
            $channel = $guild->channels->create([
                'name'      => 'Gang-Requests',
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

                $channel->setPermissions($guild->roles->get('name', '@everyone'),
                    [], ['view_channel'])->done(function () use ($channel, $guild) {
                    $channel->setPermissions($guild->roles->get('id', '1218998274791440415'), ['view_channel'],
                        ['send_messages', 'attach_files', 'add_reactions'])->done();
                });
            })->done();

            return;
        } catch (\ErrorException $e) {
            return;
        }
    }

}