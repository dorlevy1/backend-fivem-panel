<?php


namespace App\Repositories;

use App\Models\Admin;
use App\Models\PendingPermission;
use App\Models\Permission;
use App\Notifications\GrantedAccessNotification;
use App\Notifications\WebhookNotification;

class DiscordRepository
{

    protected Admin $admin;


    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    public function getOrSave($userData, $token)
    {
        $pending_permissions = PendingPermission::where('discord_id', '=', strval($userData->id))->first();
        $permissions = Permission::where('discord_id', '=', strval($userData->id))->first();

        if (!$pending_permissions && !$permissions) {
            return (object)[
                [
                    'message' => 'Unauthorized',
                    'success' => false
                ]
            ];
        }

        $admin = Admin::firstOrCreate(
            [
                'discord_id' => strval($userData->id),
            ],
            [
                'avatar' => $userData->avatar,
                'username' => $userData->username,
                'global_name' => $userData->global_name,
                'remember_token' => $token
            ]);
        $admin->update(['remember_token' => $token]);

        if ($pending_permissions && !$permissions) {
            Permission::create([
                'discord_id' => $pending_permissions->discord_id,
                'permission_type' => $pending_permissions->permission_type
            ]);

            $token = $this->attempt($pending_permissions->discord_id);

            if ($token) {
                $user = auth()->user();
                $user->notify(new GrantedAccessNotification($userData->id));
                $user->notify(new WebhookNotification([
                    'admin_discord' => $userData->id,
                    'title' => 'Granted Access For The First Time In DLPanel',
                    'description' => "<@{$userData->id}> Granted Access Successfully!",
                    'webhook' => "permissions",
                    'fields' => [],
                    'components' => [],
                ]));

            }
            $pending_permissions->delete();
        }

        return (object)['admin' => $admin];
    }


    public function attempt($discord_id)
    {
        return auth()->attempt(['discord_id' => $discord_id]);

    }

    public function login($data)
    {

        if (!$data) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $token = $this->attempt($data->admin->discord_id);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'user' => auth()->user(),
            'permissions' => auth()->user()->permissions,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }
}
