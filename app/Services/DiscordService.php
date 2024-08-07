<?php


namespace App\Services;

use App\Helpers\AccessToken;
use App\Helpers\API;
use App\Helpers\Discord\DiscordAPI;
use App\Models\Player;
use App\Models\Webhook;
use App\Notifications\WebhookNotification;
use App\Repositories\DiscordRepository;
use Illuminate\Http\Request;
use PHPUnit\Runner\ErrorException;

class DiscordService
{

    protected string $guild_id;
    //Discord oauth service url
    protected string $tokenURL = 'https://discord.com/api/oauth2/token';

    //Discord api url

    //Discord Bot Token

    //Token request required data
    protected array $tokenData = [
        'client_id' => null,
        'client_secret' => null,
        'grant_type' => 'authorization_code',
        'code' => '',
        'redirect_uri' => null,
        'scope' => null,
    ];

    //User service constructor
    protected AccessToken|null $tokens;
    private DiscordRepository $discordRepository;
    private API $api;

    public function __construct(DiscordRepository $discordRepository)
    {
        $this->discordRepository = $discordRepository;
        $this->tokenData['client_id'] = config('discord.client_id');
        $this->tokenData['client_secret'] = config('discord.client_secret');
        $this->tokenData['scope'] = config('discord.scopes');
        $this->tokenData['redirect_uri'] = config('discord.redirect_uri');
        $this->api = new API();
        $this->discord = new DiscordAPI();
        $this->guild_id = env('DISCORD_BOT_GUILD');
    }

    public function setCode($code): void
    {
        $this->tokenData['code'] = $code;
    }

    public function setRedirect($uri): void
    {
        $this->tokenData['redirect_uri'] = $uri;
    }

    public function login($data)
    {
        $this->setCode($data->get('code') ?? $data->code);
        $this->setRedirect($data->redirect_to ?? config('discord.redirect_uri'));
        $authUser = $this->auth();


        return $this->discordRepository->login($authUser);
    }


    public function getTokens(): AccessToken
    {
        $tokens = $this->api->apiRequest($this->tokenURL, $this->tokenData);
        return new AccessToken($tokens);

    }

    public function auth()
    {
        try {

            $this->tokens = $this->getTokens();
            $userData = $this->discord->getUser($this->tokens->getToken());

            if ($userData->id) {
                return $this->discordRepository->getOrSave($userData, $this->tokens->getToken());
            }

            return false;

        } catch (ErrorException $e) {
            return $e->getMessage();
        }
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

        if (!$this->checkForOnlinePlayer($request)) {
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
