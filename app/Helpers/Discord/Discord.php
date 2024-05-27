<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use Discord\Discord as DiscordPHP;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

#[AllowDynamicProperties] class Discord extends Client
{

    private string $globalPathCommands = '\App\Helpers\Discord\Commands\\';
    private string $globalPathFeatures = '\App\Helpers\Discord\Features\\';
    private string $directoryCommands = __DIR__ . '/Commands';
    private string $featureDirectory = __DIR__ . '/Features';

    public function __construct()
    {
        parent::__construct();
        $this->entries = scandir($this->directoryCommands);
        $this->features = scandir($this->featureDirectory);
        $this->featuresClasses = [];
        $this->commandClasses = [];

        $this->client->on('init', function (DiscordPHP $d) {
            $this->generate_features($d);
            $this->generate_commands($d);

            $d->on(Event::MESSAGE_CREATE, function (Message $message, DiscordPHP $discord) {
                echo "{$message->author->username}: {$message->content}", PHP_EOL;
            });

            $d->on(Event::INTERACTION_CREATE, function ($interaction, DiscordPHP $discordPHP) {
                new Interaction($this->client, $discordPHP, $interaction);
                $this->interactions($interaction);

            });
        });

        $this->client->run();
    }

    private function interactions($d)
    {
        $array = array_merge($this->featuresClasses, $this->commandClasses);

        foreach ($array as $class) {
            $class->interaction($d, $this->client);
        }
    }

    private function generate_features($d): void
    {

        foreach ($this->features as $feature) {
            if ($feature !== '.' && $feature !== '..') {
                $path = $this->featureDirectory . '/' . $feature;
                if (is_file($path)) {
                    $class = $this->globalPathFeatures . str_replace('.php', '', $feature);
                    $this->featuresClasses[] = new $class($d, $this->client);
                }
            }
        }
    }

    private function generate_commands($d): void
    {

        foreach ($this->entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $path = $this->directoryCommands . '/' . $entry;
                if (is_file($path)) {
                    $class = $this->globalPathCommands . str_replace('.php', '', $entry);
                    $this->commandClasses[] = new $class($d, $this->client);
                }
            }
        }
    }
}