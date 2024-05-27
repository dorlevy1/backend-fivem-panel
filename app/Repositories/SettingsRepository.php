<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Helpers\Discord\DiscordMessage;
use App\Message;
use App\Models\Player;
use App\Models\Settings;
use App\Models\Warn;

#[AllowDynamicProperties] class SettingsRepository extends Message
{


    protected Settings $settings;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;


    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
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