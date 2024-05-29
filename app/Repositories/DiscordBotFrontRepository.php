<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Helpers\Discord\DiscordMessage;
use App\Message;
use App\Models\DiscordBot;
use App\Models\Player;
use App\Models\Settings;
use App\Models\Warn;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

#[AllowDynamicProperties] class DiscordBotFrontRepository
{


    protected DiscordBot $discordBotFront;

    public function __construct(DiscordBot $discordBotFront)
    {
        $this->discordBotFront = $discordBotFront;
    }

    public function all()
    {

        return $this->discordBotFront->all();
    }

    private function updateInGameNotify($name): void
    {
    }

    public function update($request)
    {

        $data = DiscordBot::category($request['category'])->where('label', '=', $request['label'])->first();

        $data->value = $request['category'] === 'Auth' ? Crypt::encryptString(json_encode($request['value'])) : json_encode($request['value']);
        $data->save();

        return $data;
    }

    public function delete($id)
    {

    }

    private function sendSocket($data): void
    {

    }

}