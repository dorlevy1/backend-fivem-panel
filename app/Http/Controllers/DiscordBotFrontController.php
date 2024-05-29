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

    public function all()
    {
        return $this->discordService->all();
    }

    public function update(Request $request)
    {

        return $this->discordService->update($request->data);
    }
}
