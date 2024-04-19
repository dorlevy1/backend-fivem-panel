<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Admin;
use App\Models\Ban;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminRepository
{

    protected Admin $admins;

    public DatabaseChange $onlineNotify;
    public DatabaseChange $notify;

    public function __construct(Admin $admins)
    {
        $this->admins = $admins;
        $this->notify = new DatabaseChange('adminsUpdate', 'my-event');
        $this->onlineNotify = new DatabaseChange('onlinePlayersUpdate', 'my-event');
    }

    public function checkForPermissions(): \Illuminate\Http\JsonResponse
    {

        if (Auth::check()) {
            return response()->json([
                'permissions' => auth()->user()->permissions,
            ]);
        }

        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }
}
