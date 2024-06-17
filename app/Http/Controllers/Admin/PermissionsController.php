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

    public function addPlayer(Request $request)
    {
        return $this->permissionsService->addPlayer($request);
    }

    public function show($discord_id)
    {
    }

    public function update(Request $request)
    {
        return $this->permissionsService->update($request);
    }

    public function delete($id)
    {
        return $this->permissionsService->delete($id);
    }

    public function pending_delete($id)
    {
        return $this->permissionsService->pending_delete($id);
    }
}
