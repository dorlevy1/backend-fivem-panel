<?php

namespace App\Console\Commands;

use AllowDynamicProperties;
use App\Enums\Discord;
use App\Helpers\API;
use App\Helpers\Discord\DiscordAPI;
use App\Helpers\Discord\DiscordBot;
use App\Helpers\Discord\DiscordMessage;
use App\Helpers\Discord\Features\CreateGangButton;
use App\Models\Webhook;
use Discord\Parts\Channel\Channel;
use Illuminate\Console\Command;

#[AllowDynamicProperties] class InitGangCreationArea extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:bot-gangs-area';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        try {
//            $guild = $this->discord->bot->guilds->get('id', env('DISCORD_BOT_GUILD'));
//            if (array_search('Gang Create Area', array_column($guild->channels->toArray(), 'name'))) {
//                $this->error('Channels Already Exists !');
//
//                return false;
//            }
//
//            $group = $guild->channels->create([
//                'name' => 'Gang Create Area',
//                'type' => Channel::TYPE_CATEGORY,
//                'nsfw' => false
//            ]);
//            $guild->channels->save($group)->then(function (Channel $channel) use ($guild) {
//
//                Webhook::updateOrCreate([
//                    'name' => $channel->name
//                ], [
//                    'name'       => $channel->name,
//                    'channel_id' => $channel->id,
//                    'parent'     => true
//                ]);
//
//                $channel = $guild->channels->create([
//                    'name'      => 'Join-To-Gang',
//                    'type'      => Channel::TYPE_TEXT,
//                    'parent_id' => $channel->id,
//                    'nsfw'      => false
//                ]);
//                $guild->channels->save($channel)->then(function (Channel $channel) {
//                    Webhook::updateOrCreate([
//                        'name' => $channel->name
//                    ], [
//                        'name'       => $channel->name,
//                        'channel_id' => $channel->id,
//                        'parent'     => true
//                    ]);
//                })->done();
//            })->done();
//
//            $this->info('Initialization for Gang Creation finished successfully');
//            $this->discord->bot->close();
//        } catch (\ErrorException $e) {
//            return $e->getMessage();
//        }
    }
}
