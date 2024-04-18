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

    public function checkForPermissions($data)
    {

        $token = auth()->attempt(['discord_id' => '604330997630238726']);

        if ($token && Auth::check()) {
            if (is_null(auth()->user()->permissions)) {

                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
            $permissions = explode(',', json_decode(auth()->user()->permissions)->scopes);

            if (in_array('staff', $permissions) || in_array('*', $permissions)) {
                return response()->json([
                    'user' => auth()->user(),
                    'authorization' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ]);
            }
        }
        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }
}
