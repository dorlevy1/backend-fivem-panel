<?php

namespace App\Http\Controllers;

use App\Services\DiscordBotFrontService;
use Illuminate\Http\Request;

class DiscordBotFrontController extends Controller
{

    private DiscordBotFrontService $discordService;

    public function __construct(DiscordBotFrontService $discordService)
    {
        $this->discordService = $discordService;
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
