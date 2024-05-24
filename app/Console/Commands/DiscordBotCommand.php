<?php

namespace App\Console\Commands;

use App\Helpers\Discord\Commands\GangMembers;
use App\Helpers\Discord\Commands\Permissions;
use App\Helpers\Discord\Commands\Update;
use App\Helpers\Discord\Discord;
use App\Helpers\Discord\DiscordBot;
use App\Helpers\Discord\DiscordCommand;
use App\Helpers\Discord\Features\JoinToGang;
use App\Helpers\Discord\Interaction;
use Discord\Discord as DiscordPHP;
use Discord\Exceptions\IntentException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Option;
use Discord\WebSockets\Event;
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
        new Discord();
    }
}
