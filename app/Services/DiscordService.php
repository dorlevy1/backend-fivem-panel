<?php


namespace App\Services;

use App\Helpers\AccessToken;
use App\Helpers\API;
use App\Models\Player;
use App\Repositories\DiscordRepository;
use Illuminate\Http\Request;
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
    private $api;

    public function __construct(DiscordRepository $discordRepository)
    {
        $this->discordRepository = $discordRepository;
        $this->tokenData['client_id'] = config('discord.client_id');
        $this->tokenData['client_secret'] = config('discord.client_secret');
        $this->tokenData['scope'] = config('discord.scopes');
        $this->api = new API();
    }

    public function setCode($code): void
    {
        $this->tokenData['code'] = $code;
    }

    public function setRedirect($uri): void
    {
        $this->tokenData['redirect_uri'] = $uri;
    }


    public function auth()
    {
        try {
            $tokens = $this->api->apiRequest($this->tokenURL, $this->tokenData);

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
        $user = $this->api->apiRequest('https://discord.com/api/users/@me', null, $this->tokens->getToken());

        if ( !$user->mfa_enabled) {
            throw new ErrorException('You Must Enabled MFA Auth');
        }

        return $user;
    }

    public function checkIfInGuild(): object|false
    {
        $guilds = $this->api->apiRequest('https://discord.com/api/users/@me/guilds', null, $this->tokens->getToken());

        foreach ($guilds as $guild) {
            if ($guild->id === $this->guild_id) {
                return $guild;
            }
        }

        return false;
    }

    public function getOnlinePlayers(): \Illuminate\Http\JsonResponse
    {
        $players = $this->api->apiRequest(env('FIVEM_IP') . '/players.json', null, null, 'get');

        return response()->json($players);
    }

    public function checkForOnlinePlayer(Request $request)
    {
        $players = $this->api->apiRequest(env('FIVEM_IP') . '/players.json', null, null, 'get');

        foreach ($players as $player) {
            if (array_search($request->discord, $player->identifiers)) {
                return $player;
            }
        }

        return false;
    }

    public function getPlayerData(Request $request)
    {
        $playerA = [];

        if ( !$this->checkForOnlinePlayer($request)) {
            return response()->json([
                'player' => false
            ]);
        }
        $playerA['playerData'] = $this->checkForOnlinePlayer($request);

        return $this->getPlayerDataGame($playerA['playerData']->identifiers, $playerA);

    }

    private function getPlayerDataGame($identifiers, &$playerA)
    {
        foreach ($identifiers as $identifier) {
            if (str_contains($identifier, 'fivem')) {
                $playerB = $this->api->apiRequest(env('FIVEM_API') . env('FIVEM_KEY') . env('FIVEM_SEARCH') . str_replace('fivem:',
                        '', $identifier),
                    null,
                    null, 'get');

                $playerA['playerTime'] = $playerB[0]->seconds;
            }

            if (str_contains($identifier, 'license')) {
                $playerA['playerDataGame'] = Player::where('license', '=', $identifier)->get();
            }
        }

        return response()->json($playerA);


    }
}