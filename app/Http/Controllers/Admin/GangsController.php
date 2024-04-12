<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GangService;
use App\Services\PlayerService;
use Illuminate\Http\Request;

class GangsController extends Controller
{

    private GangService $gangService;

    public function __construct(GangService $gangService)
    {
        $this->gangService = $gangService;
    }

    public function view()
    {
        return $this->gangService->getGangs();
    }

}
