<?php

namespace App\Http\Controllers;

use App\Services\DiscordBotFrontService;
use App\Services\GangRequestService;
use Illuminate\Http\Request;

class GangRequestsController extends Controller
{

    private GangRequestService $gangRequestService;

    public function __construct(GangRequestService $gangRequestService)
    {
        $this->gangRequestService = $gangRequestService;
    }

    public function handle(Request $request): void
    {

    }

    public function getOnlinePlayers()
    {
    }

    public function getPlayerData(Request $request)
    {
    }

    public function checkForOnlinePlayer(Request $request)
    {
    }
}
