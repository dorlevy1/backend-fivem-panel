<?php


namespace App\Repositories;

use App\Models\Admin;
use App\Models\Gang;
use App\Models\Permission;
use App\Models\Player;
use App\Models\User;
use App\Services\PlayerService;
use Illuminate\Support\Facades\DB;

class PermissionsRepository
{

    protected Permission $permissions;

    public function __construct(Permission $permissions)
    {
        $this->permissions = $permissions;
    }


    public function get(): object
    {

        $permissions = [];
        foreach ($this->permissions->all() as $permission) {
            $user = Admin::where('discord_id', '=', $permission->discord_id)->get();
            $permissions[] = $user;
        }
        $pending = DB::table('pending_permissions')->get();

        return (object)['permissions' => $permissions, 'pending' => $pending];
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