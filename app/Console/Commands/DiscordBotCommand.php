<?php

namespace App\Console\Commands;

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
        new Discord();
    }
}
