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
}
