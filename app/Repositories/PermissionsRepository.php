<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Admin;
use App\Models\Gang;
use App\Models\PendingPermission;
use App\Models\Permission;
use App\Models\Player;
use App\Models\User;
use App\Services\PlayerService;
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
                str_replace('discord:', '', $data->player['metadata']['discord']));
            $permissionExists = Permission::where('discord_id', '=',
                str_replace('discord:', '', $data->player['metadata']['discord']));

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

                return 'dor1';
            }

            if ( !$pendingExists && !$permissionExists) {
                $permissions = PendingPermission::firstOrCreate([
                    'discord_id' => str_replace('discord:', '', $data->player['metadata']['discord']),
                    'scopes'     => 'staff'
                ]);

                $this->notify->setData([
                    'permissions' => $this->all()[0],
                    'pending'     => $this->all()[1]
                ]);
                $this->notify->send($this->notify);


                return response()->json([
                    'user'    => $permissions,
                    'message' => 'Admin Added to Pending Permissions'
                ]);
            }
        }

        $permissions = PendingPermission::firstOrCreate([
            'discord_id' => str_replace('discord:', '', $data->player['metadata']['discord']),
            'scopes'     => 'staff'
        ]);

        $this->notify->setData([
            'permissions' => $this->all()[0],
            'pending'     => $this->all()[1]
        ]);
        $this->notify->send($this->notify);

        return response()->json([
            'user'    => $permissions,
            'message' => 'Admin Added to Pending Permissions'
        ]);

    }
}