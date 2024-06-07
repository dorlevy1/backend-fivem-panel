<?php

namespace App\Console\Commands;

use App\Enums\Discord;
use App\Helpers\API;
use App\Helpers\Discord\DiscordAPI;
use App\Helpers\Discord\DiscordMessage;
use App\Message;
use App\Models\Webhook;
use Illuminate\Console\Command;

class InitWebhookDiscordChannels extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init-webhook-discord-channels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    private function checkOrInsert($datas, $guild = 'logs')
    {
        $api = new API();
        $endpoint = Discord::GUILD_CHANNELS->endpoint(['guildId' => $guild === 'logs' ? env('DISCORD_BOT_GUILD_LOGS') : env('DISCORD_BOT_GUILD')]);

        $channels = $api->apiRequest("{$endpoint}", null,
            env('DISCORD_BOT_TOKEN'), 'Bot', true, 'GET');
        if (is_object($channels)) {
            $channels = (array)$channels;
        }

        for ($i = 0; $i < count($datas); $i++) {

            $data = $datas[$i];
            $indexExists = array_search($data['name'], array_column($channels, 'name'));
            if ($indexExists) {
                $channel = $channels[$indexExists];
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name' => $channel->name,
                    'channel_id' => $channel->id,
                    'parent' => $channel->type === 4
                ]);
                $this->info($data['name'] . ' Updated!');
                if ($channel->type === 4) {
                    return $channel;
                }
            } else {

                $channel = $api->apiRequest("{$endpoint}",
                    json_encode($data),
                    env('DISCORD_BOT_TOKEN'), 'Bot', true);

                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name' => $channel->name,
                    'channel_id' => $channel->id,
                    'parent' => $channel->type === 4
                ]);

                if ($channel->type !== 4) {
                    DiscordMessage::createMessage([
                        'adminDiscordId' => 1,
                        'title' => "Initialization For " . ucfirst($channel->name),
                        'description' => ucfirst($channel->name) . " Initialization Finished Successfully.",
                        'webhook' => $channel->name,
                    ]);

                    $this->info(ucfirst($channel->name) . ' Initialization finished successfully');
                    $this->info('Check First Message In ' . ucfirst($channel->name));
                } else {
                    $this->info($channel->name . ' Initialization finished successfully');
                }
                $this->newLine();
            }
        }
        return true;
    }

    public function handle()
    {

        $api = new API();

        try {
            $mainChannel = $this->checkOrInsert([['name' => 'DLPanel', 'type' => 4]]);
            $this->checkOrInsert([
                ['name' => 'dlp-bans', 'parent_id' => $mainChannel->id],
                ['name' => 'dlp-kicks', 'parent_id' => $mainChannel->id],
                ['name' => 'dlp-warns', 'parent_id' => $mainChannel->id],
                ['name' => 'dlp-permissions', 'parent_id' => $mainChannel->id],
                ['name' => 'dlp-announcements', 'parent_id' => $mainChannel->id],
                ['name' => 'dlp-redeem-codes', 'parent_id' => $mainChannel->id],
            ]);


            $this->info('Initialization for Channels finished successfully');

            $dataRole = [
                'name' => 'Bans',
                'color' => 255,
            ];
            $endpoint = Discord::CREATE_ROLE->endpoint(['guildId' => env('DISCORD_BOT_GUILD_LOGS')]);

            $exists = $api->apiRequest("{$endpoint}", null,
                env('DISCORD_BOT_TOKEN'), 'Bot', false, 'GET');
            $indexExists = array_search($dataRole['name'], array_column($exists, 'name'));

            if (!$indexExists) {
                $role = $api->apiRequest("{$endpoint}", json_encode($dataRole),
                    env('DISCORD_BOT_TOKEN'), 'Bot', true);
                if ($role) {
                    $this->info('Bans Role Created Successfully');
                }
            }
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }
}
