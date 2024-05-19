<?php


namespace App\Helpers\Discord;


use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;

class Client
{

    /**
     * @throws IntentException
     */


    public Discord $client;

    /**
     * @throws IntentException
     */
    public function __construct()
    {
        $this->client = new Discord([
            'token'          => $_ENV['DISCORD_BOT_TOKEN'],
            'loadAllMembers' => true,
            'storeMessages'  => true,
            'retrieveBans'   => true,
            'intents'        => Intents::getDefaultIntents() | Intents::GUILDS |
                Intents::GUILD_MESSAGES |
                Intents::DIRECT_MESSAGES |
                Intents::GUILD_MEMBERS |
                Intents::GUILD_MESSAGE_REACTIONS |
                Intents::MESSAGE_CONTENT
        ]);

    }

    public function __get($name)
    {
        return $this[$name];
    }

}