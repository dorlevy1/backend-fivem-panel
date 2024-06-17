<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Message;
use App\Models\ActionPermission;
use App\Models\Admin;
use App\Models\PendingPermission;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\Settings;
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
            $user = Admin::with('permissions')->where('discord_id', '=', $permission->discord_id)->first();
            $permissions[] = $user;
        }
        $pending = DB::table('pending_permissions')->get();

        $permissions_type = PermissionType::all();
        $permissions_action = ActionPermission::all();

        return [$permissions, $pending, $permissions_type, $permissions_action];
    }


    private function objects(): array
    {
       return [
            'permissions' => $this->all()[0],
            'pending' => $this->all()[1],
            'permissions_type' => $this->all()[2],
            'permissions_action' => $this->all()[3],
            'permissions_settings' => Settings::category('permissions')->get()
        ];
    }
    public function get(): object
    {
        return response()->json($this->objects());
    }

    public function addPlayer($data)
    {

        $exists = Admin::where('discord_id', '=',
            str_replace('discord:', '', $data->player['discord']))->first();

        if ($exists) {
            $pendingExists = PendingPermission::where('discord_id', '=',
                str_replace('discord:', '', $data->player['discord']))->first();
            $permissionExists = Permission::where('discord_id', '=',
                str_replace('discord:', '', $data->player['discord']))->first();

            if ($permissionExists) {

                return response()->json([
                    'user' => $permissionExists,
                    'message' => 'Admin already have permissions.'
                ]);

            }
            if ($pendingExists) {
                return response()->json([
                    'user' => $pendingExists,
                    'message' => 'Admin already have pending permissions.'
                ]);
            }

            return $this->pendingCreate($data);
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
            'discord_id' => str_replace('discord:', '', $data->player['discord']),
            'permission_type' => $data->permission_type
        ]);
//
        $this->notify->setData($this->objects());
        $this->notify->send($this->notify);

        $user = auth()->user();
        $discord = str_replace('discord:', '', $data->player['discord']);
        $user->notify(new FirstTimeNotification($discord));
//        $user->notify(new WebhookNotification([
//            'admin_discord' => $discord,
//            'id' => $discord,
//            'webhook' => "private",
//            'message' => Message::createDraft(['title' => 'Invitation For DLPanel',
//                'description' => "<@{$discord}> got an invitation!",
//                'fields' => [],
//                'components' => []])
//        ]));

        return response()->json([
            'user' => $permissions,
            'message' => 'Admin Added to Pending Permissions'
        ]);
    }


    public function update($data)
    {
        $role = $data[0];
        $user = $data[1];
        $permission = Permission::find($user['permissions']['id']);
        $permission->permission_type = $role['id'];
        $permission->save();

        $this->notify->setData($this->objects());
        $this->notify->send($this->notify);
        return $permission;
    }

    public function delete($id)
    {
        $delete = Permission::find($id)->delete();

        $this->notify->setData($this->objects());
        $this->notify->send($this->notify);


        return response()->json([
            'message' => 'User Id: ' . $id . ' Deleted From Permissions.'
        ]);

    }

    public function pending_delete($id)
    {
        $delete = PendingPermission::find($id)->delete();

        $this->notify->setData($this->objects());
        $this->notify->send($this->notify);

        return response()->json([
            'message' => 'User Id: ' . $id . ' Deleted From Permissions.'
        ]);
    }
}
