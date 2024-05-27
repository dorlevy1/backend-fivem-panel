<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Helpers\Discord\DiscordMessage;
use App\Message;
use App\Models\DiscordBotFront;
use App\Models\Player;
use App\Models\Warn;

#[AllowDynamicProperties] class DiscordBotFrontRepository extends Message
{


    protected DiscordBotFront $discordBotFront;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;


    public function __construct(DiscordBotFront $discordBotFront)
    {
        $this->discordBotFront = $discordBotFront;
        $this->notify = new DatabaseChange('warnsUpdate', 'my-event');

    }

    public function getWarns()
    {
    }

    private function updateInGameNotify($name): void
    {
    }


    public function add($data)
    {

    }

    public function update($data)
    {

    }

    public function delete($id)
    {

    }

    private function sendSocket($data): void
    {
       
    }

}