<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Models\Player;
use App\Services\DiscordService;
use Discord\Discord as DiscordPHP;
use Discord\Exceptions\IntentException;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;

#[AllowDynamicProperties] class Discord
{

    private string $globalPathCommands = '\App\Helpers\Discord\Commands\\';
    private string $globalPathFeatures = '\App\Helpers\Discord\Features\\';
    private string $directoryCommands = __DIR__ . '/Commands';
    private string $featureDirectory = __DIR__ . '/Features';
    private DiscordPHP $discord;
    protected DiscordService $service;

    public function __construct(DiscordPHP $discord)
    {
        $this->entries = scandir($this->directoryCommands);
        $this->features = scandir($this->featureDirectory);
        $this->featuresClasses = [];
        $this->commandClasses = [];
        $this->discord = $discord;


        $this->discord->on('init', function (DiscordPHP $d) {
            $this->generate_features($d);
            $this->generate_commands($d);
//
//
//            $d->on(Event::MESSAGE_CREATE, function (Message $message, DiscordPHP $discord) {
//                echo "{$message->author->username}: {$message->content}", PHP_EOL;
//            });

            $d->on(Event::INTERACTION_CREATE, function ($interaction, DiscordPHP $discordPHP) {
                new Interaction($discordPHP, $interaction);
                $this->interactions($interaction);
            });

            $d->on(Event::GUILD_MEMBER_ADD, function (Member $member, DiscordPHP $discord) {
                if ($member->guild->id === env('DISCORD_BOT_GUILD_GANGS')) {
                    $this->array_find($member->guild->roles->toArray(), function ($obj) use ($member) {
                        $player = Player::where('discord', '=', 'discord:' . $member->id)->first();

                        $name = str_replace(' ', '', strtolower($obj->name));
                        if ($player && $player->criminal->organization !== '' && $name === $player->criminal->organization) {
                            $member->addRole($obj)->done();
                            $member->addRole('1207523849109901355')->done();
                            if (isset($player->organization) && $name === $player->organization->name . 'boss') {
                                $member->addRole($obj)->done();
                            }
                        }
                    });
                }
            });
        });

        $this->discord->run();
    }


    public function array_find(array $array, callable $callback): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @throws IntentException
     */
    private function interactions($d): void
    {
        $array = array_merge($this->featuresClasses, $this->commandClasses);

        foreach ($array as $class) {
            $class->interaction($d, $this->discord);
        }
    }

    /**
     * @throws IntentException
     */
    private function generate_features($d): void
    {

        foreach ($this->features as $feature) {
            if ($feature !== '.' && $feature !== '..') {
                $path = $this->featureDirectory . '/' . $feature;
                if (is_file($path)) {
                    $class = $this->globalPathFeatures . str_replace('.php', '', $feature);
                    $this->featuresClasses[] = app()->make($class);

                }
            }
        }
    }

    /**
     * @throws IntentException
     */
    private function generate_commands($d): void
    {

        foreach ($this->entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $path = $this->directoryCommands . '/' . $entry;
                if (is_file($path)) {
                    $class = $this->globalPathCommands . str_replace('.php', '', $entry);
                    $this->commandClasses[] = new $class($d, $this->discord);
                }
            }
        }
    }
}
