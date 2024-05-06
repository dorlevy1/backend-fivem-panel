<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Admin;
use App\Models\PendingPermission;
use App\Models\Permission;
use App\Notifications\FirstTimeNotification;
use App\Notifications\WebhookNotification;
use Illuminate\Support\Facades\DB;

class PermissionsRepository
{

    protected Permission $permissions;
    protected DatabaseChange $notify;


    public function __construct(Permission $permissions)
    {
        $this->permissions = $permissions;
        $this->notify = new DatabaseChange('permissionsUpdate', 'my-event');

    }


    private function all()
    {
        $permissions = [];
        foreach ($this->permissions->all() as $permission) {
            $user = Admin::where('discord_id', '=', $permission->discord_id)->first();
            $permissions[] = $user;
        }
        $pending = DB::table('pending_permissions')->get();

        return [$permissions, $pending];
    }

    public function get(): object
    {
        return response()->json([
            'permissions' => $this->all()[0],
            'pending'     => $this->all()[1]
        ]);
    }

    public function addPlayer($data)
    {

        $exists = Admin::where('discord_id', '=',
            str_replace('discord:', '', $data->player['metadata']['discord']))->first();

        if ($exists) {
            $pendingExists = PendingPermission::where('discord_id', '=',
                str_replace('discord:', '', $data->player['metadata']['discord']))->first();
            $permissionExists = Permission::where('discord_id', '=',
                str_replace('discord:', '', $data->player['metadata']['discord']))->first();

            if ($permissionExists) {

                $this->notify->setData([
                    'permissions' => $this->all()[0],
                    'pending'     => $this->all()[1]
                ]);
                $this->notify->send($this->notify);

                return response()->json([
                    'user'    => $permissionExists,
                    'message' => 'Admin already have permissions.'
                ]);

            }
            if ($pendingExists && !$permissionExists || $pendingExists && $permissionExists) {
                Permission::firstOrCreate([
                    'discord_id' => str_replace('discord:', '', $data->player['metadata']['discord']),
                    'scopes'     => 'staff'
                ]);
                $pendingExists->delete();
                #TODO
                //RETURN STATUS CODE JSON
            }

            if ( !$pendingExists && !$permissionExists) {

                return $this->pendingCreate($data);
            }
        }

        return $this->pendingCreate($data);

    }

    /**
     * @param $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function pendingCreate($data): \Illuminate\Http\JsonResponse
    {

        $permissions = PendingPermission::firstOrCreate([
            'discord_id' => str_replace('discord:', '', $data->player['metadata']['discord']),
            'scopes'     => 'staff'
        ]);

        $this->notify->setData([
            'permissions' => $this->all()[0],
            'pending'     => $this->all()[1]
        ]);
        $this->notify->send($this->notify);

        $user = auth()->user();
        $discord = str_replace('discord:', '', $data->player['metadata']['discord']);
        $user->notify(new FirstTimeNotification(str_replace('discord:', '', $data->player['metadata']['discord'])));
        $user->notify(new WebhookNotification([
            'admin_discord' => $discord,
            'title'         => 'Invitation For DLPanel',
            'description'   => "<@{$discord}> got an invitation!",
            'webhook'       => "permissions"
        ]));

        return response()->json([
            'user'    => $permissions,
            'message' => 'Admin Added to Pending Permissions'
        ]);
    }
}