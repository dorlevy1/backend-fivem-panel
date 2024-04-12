<?php


namespace App\Services;

use App\Helpers\AccessToken;
use App\Models\User;
use App\Repositories\DiscordRepository;
use Illuminate\Support\Facades\Session;
use PHPUnit\Runner\ErrorException;

class DiscordService
{

    protected string $guild_id = '1192226332759834834';
    //Discord oauth service url
    protected string $tokenURL = 'https://discord.com/api/oauth2/token';

    //Discord api url
    protected string $baseApi = 'https://discord.com/api';

    //Discord Bot Token

    //Token request required data
    protected array $tokenData = [
        'client_id'     => null,
        'client_secret' => null,
        'grant_type'    => 'authorization_code',
        'code'          => null,
        'redirect_uri'  => null,
        'scope'         => null,
    ];

    //User service constructor
    protected AccessToken|null $tokens;
    private DiscordRepository $discordRepository;

    public function __construct(DiscordRepository $discordRepository)
    {
        $this->discordRepository = $discordRepository;
        $this->tokenData['client_id'] = config('discord.client_id');
        $this->tokenData['client_secret'] = config('discord.client_secret');
        $this->tokenData['scope'] = config('discord.scopes');
    }

    public function setCode($code): void
    {
        $this->tokenData['code'] = $code;
    }

    public function setRedirect($uri): void
    {
        $this->tokenData['redirect_uri'] = $uri;
    }

    private function apiRequest($url, $data, $token = null, $type = null, $json = null)
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ( !is_null($data) && is_null($json)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ( !is_null($data) && $json) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers[] = 'Accept: application/json';
        if ( !is_null($token) && is_null($type)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if ( !is_null($token) && !is_null($type)) {
            $headers[] = 'Authorization: Bot ' . $token;
        }


        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        return json_decode($response);
    }


    //Handles oauth2 callback and returns access token
    public function auth()
    {
        try {
            $tokens = $this->apiRequest($this->tokenURL, $this->tokenData);
            $this->tokens = new AccessToken($tokens);
            if ($this->getUser()->id) {
                return $this->discordRepository->getOrSave($this->getUser(), $this->tokens->getToken());
            }

            return false;

        } catch (ErrorException $e) {
            return $e->getMessage();
        }
    }

    private function getUser(): object
    {
        $user = $this->apiRequest('https://discord.com/api/users/@me', null, $this->tokens->getToken());

        if ( !$user->mfa_enabled) {
            throw new ErrorException('You Must Enabled MFA Auth');
        }

        return $user;
    }

    public function checkIfInGuild(): object|false
    {
        $guilds = $this->apiRequest('https://discord.com/api/users/@me/guilds', null, $this->tokens->getToken());

        foreach ($guilds as $guild) {
            if ($guild->id === $this->guild_id) {
                return $guild;
            }
        }

        return false;
    }

    public function types()
    {
        //            $members_in_guild = $this->apiRequest("https://discord.com/api/guilds/{$this->guild_id}/members?limit=100",
        //                null,
        //                env('DISCORD_BOT_TOKEN'), 'Bot');
        //            $create_roll = $this->apiRequest("https://discord.com/api/guilds/{$this->guild_id}/roles", [
        //                'name'        => 'D.D.L System Check Auto',
        //                'color'       => '(255,255,0)',
        //                'hoist'       => true,
        //                'mentionable' => true,
        //            ], env('DISCORD_BOT_TOKEN'), 'Bot');
        //
    }
}