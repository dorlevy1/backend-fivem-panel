<?php

namespace App\Console\Commands;

use App\Enums\Discord;
use App\Helpers\API;
use App\Helpers\Discord\DiscordAPI;
use App\Helpers\Discord\DiscordMessage;
use App\Models\Webhook;
use Illuminate\Console\Command;

class InitWebhookDiscordChannels extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dlpanel:init-webhook-discord-channels';

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

        $api = new API();
        $message = new DiscordMessage();

        try {
            $endpoint = Discord::GUILD_CHANNELS->endpoint(['guildId' => env('DISCORD_BOT_GUILD_LOGS')]);
            $channels = $api->apiRequest("{$endpoint}", null,
                env('DISCORD_BOT_TOKEN'), 'Bot', true, 'GET');
            if (array_search('DLPanel', array_column($channels, 'name'))) {
                $this->error('Channels Already Exists !');

                return false;
            }

            $channelsArray = [];

            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'DLPanel', 'type' => 4]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);

            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'Bans', 'parent_id' => $channelsArray[0]->id]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);
            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'Kicks', 'parent_id' => $channelsArray[0]->id]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);
            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'Warns', 'parent_id' => $channelsArray[0]->id]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);
            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'Permissions', 'parent_id' => $channelsArray[0]->id]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);
            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'Announcements', 'parent_id' => $channelsArray[0]->id]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);
            $channelsArray[] = $api->apiRequest("{$endpoint}",
                json_encode(['name' => 'redeem-codes', 'parent_id' => $channelsArray[0]->id]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);

            foreach ($channelsArray as $channel) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name'       => $channel->name,
                    'channel_id' => $channel->id,
                    'parent'     => $channel->type === 4
                ]);
                if ($channel->type !== 4) {
                    $message->createMessage([
                        'adminDiscordId' => 1,
                        'title'          => "Initialization For " . ucfirst($channel->name),
                        'description'    => ucfirst($channel->name) . " Initialization Finished Successfully.",
                        'webhook'        => $channel->name,
                    ]);

                    $this->info(ucfirst($channel->name) . ' Initialization finished successfully');
                    $this->info('Check First Message In ' . ucfirst($channel->name));
                } else {
                    $this->info($channel->name . ' Initialization finished successfully');
                }
                $this->newLine();
            }
            $this->info('Initialization for Channels finished successfully');

            $dataRole = [
                'name'  => 'Bans',
                'color' => 255,
            ];
            $endpoint = Discord::CREATE_ROLE->endpoint(['guildId' => env('DISCORD_BOT_GUILD_LOGS')]);
            $role = $api->apiRequest("{$endpoint}", json_encode($dataRole),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);
            if ($role) {
                $this->info('Bans Role Created Successfully');
            }
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }
}
