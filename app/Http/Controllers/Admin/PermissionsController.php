<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermissionsService;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{

    private PermissionsService $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }


    public function get()
    {
        return $this->permissionsService->get();
    }

    public function addPlayer($discord_id)
    {
    }

    public function show($discord_id)
    {
    }

    public function update(Request $request)
    {
    }

    public function delete(Request $request)
    {
    }
}
