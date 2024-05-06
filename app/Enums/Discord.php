<?php

namespace App\Enums;

enum Discord
{

    case ADD_PIN;
    case SEND_MESSAGE;
    case ME;
    case MEMBER_GUILD;
    case GUILD_CHANNELS;

    public function endpoint($data = []): string
    {
        $data = (object)$data;

        $baseUrl = 'https://discord.com/api';

        return match ($this) {
            Discord::ADD_PIN => "{$baseUrl}/channels{$data->channelId}/pins/{$data->messageId}",
            Discord::SEND_MESSAGE => "{$baseUrl}/channels/{$data->channelId}/messages",
            Discord::MEMBER_GUILD => "{$baseUrl}/guilds/{$data->guildId}/members/{$data->memberId}",
            Discord::GUILD_CHANNELS => "{$baseUrl}/guilds/{$data->guildId}/channels",
            Discord::ME => "{$baseUrl}/users/@me",
        };
    }
}