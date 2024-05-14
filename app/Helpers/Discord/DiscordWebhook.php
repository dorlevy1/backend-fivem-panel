<?php

namespace App\Helpers\Discord;

use App\Enums\DiscordWebhook as Webhook;

class DiscordWebhook
{

    public DiscordAPI $api;

    public string $type;

    public array $message;

    public function __construct($type, $message)
    {
        $this->api = new DiscordAPI();
        $this->type = $type;
        $this->message = $message;

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
        }
    }


    public function send()
    {

        switch ($this->type) {
            case Webhook::BAN->value:
                return $this->banWebhook($this->message);
            case Webhook::KICKS->value:
                return $this->kickWebhook($this->message);
            case Webhook::ANNOUNCE->value:
                return $this->announceWebhook($this->message);
            case Webhook::WARNS->value:
                return $this->warnWebhook($this->message);
            case Webhook::REDEEM->value:
                return $this->redeemWebhook($this->message);
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
}
