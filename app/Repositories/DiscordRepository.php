<?php


namespace App\Repositories;

use App\Models\Admin;
use App\Models\PendingPermission;
use App\Models\Permission;

class DiscordRepository
{

    protected Admin $admin;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    public function getOrSave($userData, $token)
    {
        $pending_permissions = PendingPermission::where('discord_id', '=', strval($userData->id));
        $permissions = Permission::where('discord_id', '=', strval($userData->id));

        if (empty($pending_permissions) && empty($permissions)) {
            return response()->json([
                'message' => 'Unauthorized',
                'success' => false
            ], 401);
        }

        $admin = Admin::firstOrCreate(
            [
                'discord_id' => strval($userData->id),
            ],
            [
                'avatar'         => $userData->avatar,
                'username'       => $userData->username,
                'global_name'    => $userData->global_name,
                'remember_token' => $token
            ]);
        $admin->update(['remember_token' => $token]);


        $pending_permissions = PendingPermission::where('discord_id', '=', strval($userData->id));
        $permissions = Permission::where('discord_id', '=', strval($userData->id));

        if ( !empty($pending_permissions->get()) && empty($permissions)) {
            Permission::create([
                'discord_id' => $pending_permissions->get()[0]->discord_id,
                'scopes'     => $pending_permissions->get()[0]->scopes
            ]);
            $pending_permissions->delete();
        }

        return response()->json([
            'admin' => $admin,
        ]);
    }
}