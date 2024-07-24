<?php


namespace App\Helpers\Discord;


use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;

class ClientService
{

    /**
     * @throws IntentException
     */


    private Discord $client;
    private static ?ClientService $instance = null;


    /**
     * @throws IntentException
     */

    public static function getInstance(): ?ClientService
    {
        if (self::$instance == null) {
            self::$instance = new ClientService();
        }
        return self::$instance;
    }

    /**
     * @throws IntentException
     */
    public function getClient(): Discord
    {
        return $this->client ?? $this->client = new Discord([
            'token' => env('DISCORD_BOT_TOKEN'),
            'loadAllMembers' => true,
            'storeMessages' => true,
            'retrieveBans' => true,
            'intents' => Intents::getDefaultIntents() | Intents::GUILDS |
                Intents::GUILD_MESSAGES |
                Intents::DIRECT_MESSAGES |
                Intents::GUILD_MEMBERS |
                Intents::GUILD_MESSAGE_REACTIONS |
                Intents::MESSAGE_CONTENT
        ]);
    }

}
