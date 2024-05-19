<?php

namespace App\Helpers\Discord;

use App\Helpers\Discord\Commands\GangMembers;
use App\Helpers\Discord\Commands\Permissions;
use App\Helpers\Discord\Commands\Update;
use App\Helpers\Discord\Features\CreateGangButton;
use Discord\Discord as DiscordPHP;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class Discord extends Client
{

    public function __construct()
    {
        parent::__construct();

        $this->client->on('init', function (DiscordPHP $d) {
            new CreateGangButton($d, $this->client);
            new GangMembers($d, $this->client);
            new Permissions($d, $this->client);
            new Update($d, $this->client);

            $d->on(Event::MESSAGE_CREATE, function (Message $message, DiscordPHP $discord) {
                echo "{$message->author->username}: {$message->content}", PHP_EOL;
            });

            $d->on(Event::INTERACTION_CREATE, function ($interaction, DiscordPHP $discordPHP) {
                new \App\Helpers\Discord\Interaction($this->client, $discordPHP, $interaction);
            });
        });

        $this->client->run();
    }

}