<?php

namespace App\Http\Controllers;

use App\Services\RedeemCodeRequestService;
use Illuminate\Http\Request;

class RedeemCodeRequestController extends Controller
{

    private RedeemCodeRequestService $redeemCodeRequestService;

    public function __construct(RedeemCodeRequestService $redeemCodeRequestService)
    {
        $this->redeemCodeRequestService = $redeemCodeRequestService;
    }

    public function handle(Request $request): void
    {

    }

    public function all()
    {
        return $this->redeemCodeRequestService->all();
    }

    public function getPlayerData(Request $request)
    {
    }

    public function checkForOnlinePlayer(Request $request)
    {
    }
}
