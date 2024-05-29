<?php

namespace App\Http\Controllers;

use App\Services\DiscordBotFrontService;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function handle(Request $request): void
    {

    }

    public function all()
    {
        return $this->settingsService->all();
    }

    public function update(Request $request)
    {

        return $this->settingsService->update($request->data);
    }


}
