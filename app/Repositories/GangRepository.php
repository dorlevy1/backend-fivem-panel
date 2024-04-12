<?php


namespace App\Repositories;

use App\Models\Gang;
use App\Models\Player;
use App\Services\PlayerService;
use Illuminate\Support\Facades\DB;

class GangRepository
{

    protected Gang $gangs;

    public function __construct(Gang $gangs)
    {
        $this->gangs = $gangs;
    }


    public function inventory($gangName)
    {
        $inventory = DB::connection('second_db')->table('ox_inventory')->where('name', '=',
            $gangName)->first() ?? [];

        return !empty($inventory) ? json_decode($inventory->data) : [];


    }

    public function getGangs()
    {

        $gangs = [];
        foreach ($this->gangs->all() as $gang) {
            $owner = Player::where('citizenid', '=', $gang->owner)->first();

            $gang->owner = $owner->charinfo->firstname . ' ' . $owner->charinfo->lastname . ' | ' . $gang->owner;
            $gang->zones = count(json_decode($gang->zones, 1));
            $gang->amountPlayers = count($gang->players);
            $gang->inventory = $this->inventory($gang->name);
            $gangs[] = $gang;
        }


        return $gangs;
    }


}