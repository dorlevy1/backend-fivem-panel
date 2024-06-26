<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlayerService;
use Illuminate\Http\Request;

class PlayersController extends Controller
{

    private PlayerService $playerService;

    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
    }

    public function view()
    {
        return $this->playerService->getPlayers();
    }

    public function playerConnecting(Request $request)
    {

        return $this->playerService->playerConnecting($request);
    }

    public function playerDisconnect(Request $request): true
    {
        return $this->playerService->playerDisconnect($request);
    }

    public function getJoinedPlayers(): object
    {
        return $this->playerService->getJoinedPlayers();
    }

    public function getPlayer($citizenid)
    {

        return $this->playerService->getPlayer($citizenid);
    }

    public function getInventory($citizenid)
    {

        return $this->playerService->getInventory($citizenid);
    }

    public function update(Request $request)
    {
    }

    public function delete(Request $request)
    {
    }

    public function give_ban(Request $request)
    {
        $this->playerService->giveBan($request->get('citizenid'));
    }
}
