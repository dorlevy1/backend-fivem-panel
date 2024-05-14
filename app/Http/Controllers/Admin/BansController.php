<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BanService;
use Illuminate\Http\Request;

class BansController extends Controller
{

    private BanService $banService;

    public function __construct(BanService $banService)
    {
        $this->banService = $banService;
    }

    public function view()
    {
        return $this->banService->getBans();
    }

    public function add(Request $request)
    {

        return $this->banService->add($request);
    }

    public function update(Request $request)
    {

        return $this->banService->update();
    }

    public function delete(Request $request)
    {

        return $this->banService->delete($request->id);
    }
}
