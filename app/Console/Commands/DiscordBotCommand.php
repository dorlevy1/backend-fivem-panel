<?php

namespace App\Console\Commands;

use App\Helpers\API;
use App\Helpers\Discord\Discord;
use Illuminate\Console\Command;

class DiscordBotCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:discord-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Discord Bot';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $available = (new API())->apiRequest("https://discord.com/api/v8/guilds/".env('DISCORD_BOT_GUILD'),
            null,
            env('DISCORD_BOT_TOKEN'), 'Bot', true, 'GET');
        if (isset($available->message)) {
            var_dump('Not Good');
        } else {
            var_dump('Good');
        }
                new Discord();
    }
}
