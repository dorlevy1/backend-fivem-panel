<?php

namespace App\Helpers\Discord;


use App\Enums\Discord;
use App\Helpers\API;
use App\Models\Webhook;
use PHPUnit\Runner\ErrorException;

class DiscordAPI
{


    private string $guild_id;
    private API $api;

    public function __construct()
    {
        $this->api = new API();
        $this->guild_id = env('DISCORD_BOT_GUILD');
    }

    public function createMessage($title, $description, $fields = [], $components = []): array
    {

        return [
            "embeds" => [
                [
                    "title"       => $title,
                    "author"      => [
                        "name"     => "DLPanel",
                        "icon_url" => "https://cdn.discordapp.com/attachments/1236147966390046732/1236387837394288830/Screenshot_2024-05-04_at_21.43.40.png?ex=6637d367&is=663681e7&hm=7549a2544ea9a978a062b984f9889235a203b59b3ec8ece64840148762a05425&",
                        "url"      => "https://discord.js.org"
                    ],
                    "thumbnail"   => [
                        "url" => "https://cdn.discordapp.com/attachments/1236147966390046732/1236387576659841235/image.png?ex=6637d329&is=663681a9&hm=c2abe6e118a47548571b3f9110ee40f406995d087577ac07e6e7368c1736fe0d&"
                    ],
                    "description" => $description,
                    "timestamp"   => date("c"),
                    "footer"      => [
                        "text" => "DLPanel By D.D.L"
                    ],
                    "fields"      => $fields
                ]
            ],
            ...$components
        ];
    }

    private function getChannel($user)
    {
        try {
            $endpoint = Discord::ME->endpoint();

            $data = $this->api->apiRequest("{$endpoint}/channels", json_encode(['recipient_id' => $user->id ?? $user]),
                env('DISCORD_BOT_TOKEN'), 'Bot', true);

            return $data->id;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

    public function getUser($token = null)
    {

        try {
            $me = Discord::ME->endpoint();
            $user = $this->api->apiRequest("{$me}", null, env('DISCORD_BOT_TOKEN'), 'Bot');
            if ( !is_null($token)) {
                $user = $this->api->apiRequest("{$me}", null, $token);
                if ( !$user->mfa_enabled) {
                    throw new ErrorException('You Must Enabled MFA Auth');
                }
            }

            return $this->checkIfInGuild($user->id) ? $user : false;

        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

    public function checkIfInGuild($id): bool
    {

        $me = Discord::MEMBER_GUILD->endpoint(['guildId' => $this->guild_id, 'memberId' => $id]);

        $exists = $this->api->apiRequest("{$me}", null, env('DISCORD_BOT_TOKEN'), 'Bot');

        if ($exists->user) {
            return true;
        }

        return false;
    }

    public function sendMessage($message, $data)
    {
        $id = match ($data['type']) {
            'user' => $this->getChannel($data['id']),
            'webhook' => Webhook::where('name', '=', $data['name'])->first()->channel_id,
        };

        try {
            $endpoint = Discord::SEND_MESSAGE->endpoint(['channelId' => $id]);

            return $this->api->apiRequest("{$endpoint}", json_encode($message), env('DISCORD_BOT_TOKEN'), 'Bot', true);
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }

}