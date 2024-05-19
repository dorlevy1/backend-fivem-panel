<?php

namespace App\Helpers\Discord;

use App\Enums\DiscordWebhook as Webhook;

class DiscordWebhook
{

    public DiscordAPI $api;

    public string $type;
    public string $id;

    public array $message;

    public function __construct($type, $message, $id = null)
    {
        $this->api = new DiscordAPI();
        $this->type = $type;
        $this->message = $message;
        $this->id = $id;

        switch ($type) {
            case Webhook::BAN->value:
                return $this->banWebhook($message);
            case Webhook::KICKS->value:
                return $this->kickWebhook($message);
            case Webhook::ANNOUNCE->value:
                return $this->announceWebhook($message);
            case Webhook::WARNS->value:
                return $this->warnWebhook($message);
            case Webhook::REDEEM->value:
                return $this->redeemWebhook($message);
            case Webhook::GANG_CREATION->value:
                return $this->gangCreationWebhook($message);
            case Webhook::PRIVATE_USER->value:
                return $this->sendPrivateMessage($message);
        }
    }

    public function banWebhook($message)
    {
        return $this->api->sendMessage($message, ['type' => 'webhook', 'name' => 'bans']);
    }

    public function warnWebhook($message)
    {
        return $this->api->sendMessage($message, ['type' => 'webhook', 'name' => 'warns']);
    }

    public function announceWebhook($message)
    {
        return $this->api->sendMessage($message, ['type' => 'webhook', 'name' => 'announce']);
    }

    public function kickWebhook($message)
    {
        return $this->api->sendMessage($message, ['type' => 'webhook', 'name' => 'kicks']);
    }

    public function redeemWebhook($message)
    {
        return $this->api->sendMessage($message, ['type' => 'webhook', 'name' => 'redeem-code']);
    }

    public function gangCreationWebhook($message)
    {
        return $this->api->sendMessage($message, ['type' => 'webhook', 'name' => 'gang-creation']);
    }

    public function sendPrivateMessage($message)
    {
        return $this->api->sendMessage($message, ['type' => 'user', 'id' => $this->id]);
    }
}
