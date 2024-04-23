<?php

namespace App\Http\Controllers;

use App\Services\DiscordService;
use Illuminate\Http\Request;

class DiscordController extends Controller
{

    private DiscordService $discordService;

    public function __construct(DiscordService $discordService)
    {
        $this->discordService = $discordService;
    }

    public function handle(Request $request): void
    {
        $this->discordService->setCode($request->get('code'));
        $this->discordService->auth();
    }

    public function getOnlinePlayers()
    {

        return $this->discordService->getOnlinePlayers();
    }

    public function getPlayerData(Request $request)
    {
        return $this->discordService->getPlayerData($request);
    }

    public function checkForOnlinePlayer(Request $request)
    {
        return $this->discordService->checkForOnlinePlayer($request);
    }
}
