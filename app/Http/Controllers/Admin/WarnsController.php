<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BanService;
use App\Services\WarnService;
use Illuminate\Http\Request;


class WarnsController extends Controller
{


    private WarnService $warnService;

    public function __construct(WarnService $warnService)
    {
        $this->warnService = $warnService;
    }

    public function view()
    {
        return $this->warnService->getWarns();
    }

    public function add(Request $request)
    {

        return $this->warnService->add($request);
    }

    public function update(Request $request)
    {

        return $this->warnService->update();
    }

    public function delete(Request $request)
    {

        return $this->warnService->delete($request->id);
    }
}
