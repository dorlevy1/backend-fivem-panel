<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Helpers\Discord\Features\CreateGangButton;
use Discord\Discord as DiscordPHP;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

#[AllowDynamicProperties] class Discord extends Client
{

    private string $globalPathCommands = '\App\Helpers\Discord\Commands\\';
    private string $directoryCommands = __DIR__ . '/Commands';

    public function __construct()
    {
        parent::__construct();
        $this->entries = scandir($this->directoryCommands);

        $this->client->on('init', function (DiscordPHP $d) {
            new CreateGangButton($d, $this->client);
            $this->generate_commands($d);

            $d->on(Event::MESSAGE_CREATE, function (Message $message, DiscordPHP $discord) {
                echo "{$message->author->username}: {$message->content}", PHP_EOL;
            });

            $d->on(Event::INTERACTION_CREATE, function ($interaction, DiscordPHP $discordPHP) {
                new \App\Helpers\Discord\Interaction($this->client, $discordPHP, $interaction);
            });
        });

        $this->client->run();
    }

    private function generate_commands($d): void
    {

        foreach ($this->entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $path = $this->directoryCommands . '/' . $entry;
                if (is_file($path)) {
                    $class = $this->globalPathCommands . str_replace('.php', '', $entry);
                    new $class($d, $this->client);
                }
            }
        }
    }
}