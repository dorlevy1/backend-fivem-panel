<?php


namespace App\Repositories;

use App\Models\Ban;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PlayerRepository
{

    protected Player $players;

    public function __construct(Player $players)
    {
        $this->players = $players;
    }

    public function getPlayers()
    {
        $players = [];
        foreach ($this->players->all() as $player) {
            $player->name = iconv("UTF-8", "UTF-8//IGNORE", $player->name);
            $players[] = $player;
        }

        return $players;
    }


    public function getPlayerByDiscord($discord = null)
    {
        foreach ($this->players as $player) {
            $metadata = $player['metadata'];
            //            auth()->user()->discord_id
            if (intval(str_replace('discord:', '', $metadata->discord)) === ($discord ?? 517410917580013568)) {
                return $player;
            }
        }

        return false;
    }

    public function getPlayerByCitizenID($citizenid = null)
    {
        return array_values(array_filter($this->getPlayers(), function ($player) use ($citizenid) {
            return $player->citizenid === $citizenid;
        }));
    }

    public function getPlayer($data)
    {
        switch ($data->type) {
            case 'discord':
                return $this->getPlayerByDiscord($data->value);
            case 'citizenid':
                return $this->getPlayerByCitizenID($data->value);
            default:
                return $this->getPlayerByDiscord();
        }
    }

    public function getInventory($citizenid)
    {
        $player = $this->getPlayerByCitizenID($citizenid);

        if ( !empty($player[0]->inventory)) {
            return ['success' => true, 'data' => $player[0]['inventory']];
        }

        return ['success' => false, 'message' => 'Theres No Inventory'];
    }

    public function giveBan($citizenid)
    {
        return $this->players->where('citizenid', '=', $citizenid)->ban;

    }
}