<?php

namespace App\Http\Controllers\API;

use App\Services\DiscordService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

class AuthController extends Controller
{

    private $discordService;

    public function __construct(DiscordService $discordService)
    {
        $this->discordService = $discordService;
    }

    public function login(Request $request)
    {
        return $this->discordService->login($request);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function checkAuth()
    {
        if (Auth::check()) {
            if (is_null(auth()->user()->permissions)) {

                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
            $permissions = explode(',', json_decode(auth()->user()->permissions)->scopes);

            if (in_array('staff', $permissions) || in_array('*', $permissions)) {
                return response()->json([
                    'success' => true,
                ]);
            }
        }

        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }

    public function refresh()
    {

        return response()->json([
            'user'          => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type'  => 'bearer',
            ]
        ]);
    }
}