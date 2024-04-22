<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Ban;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PlayerRepository
{

    protected Player $players;

    public DatabaseChange $notify;
    public DatabaseChange $warnNotify;

    public function __construct(Player $players)
    {
        $this->players = $players;
        $this->notify = new DatabaseChange('playersUpdate', 'my-event');
        $this->warnNotify = new DatabaseChange('playerWarns.' . $this->players->id, 'my-event');
    }

    public function getPlayers()
    {
        return $this->players->all();
    }

    public function getJoinedPlayers(): object
    {
        $lastDay = $this->players->lastDay()->get();
        $last7Days = $this->players->lastSevenDays()->get();

        return (object)['last_day' => $lastDay, 'last_seven_days' => $last7Days];
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
        return array_values(array_filter($this->players->all()->toArray(), function ($player) use ($citizenid) {
            return $player['citizenid'] === $citizenid;
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

        if ( !empty($player[0]['inventory'])) {
            return ['success' => true, 'data' => $player[0]['inventory']];
        }

        return ['success' => false, 'message' => 'Theres No Inventory'];
    }

    public function giveBan($citizenid)
    {
        return $this->players->where('citizenid', '=', $citizenid)->ban;

    }
}