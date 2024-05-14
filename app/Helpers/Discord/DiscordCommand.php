<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Models\Webhook;
use Discord\Discord as DiscordPHP;
use App\Notifications\WebhookNotification;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction as In;

#[AllowDynamicProperties] class DiscordCommand extends DiscordMessage
{

    public CommandBuilder $command;
    public string $commandName;

    public function __construct(DiscordPHP $discord, $commandName, $commandDescription)
    {
        parent::__construct();
        $this->discordAPI = new DiscordAPI();
        $this->commandName = $commandName;
        $this->discord = $discord;
        $this->command = $this->create($commandName, $commandDescription);
    }

    public function create($commandName, $commandDescription): CommandBuilder
    {
        return CommandBuilder::new()->setName($commandName)->setDescription($commandDescription);
    }

    public function addOption($name, $description, $type)
    {
        return (new Option($this->discord))
            ->setName($name)
            ->setDescription($description)
            ->setType($type);

    }

    public function save($data)
    {
        $this->discord->application->commands->save($this->discord->application->commands->create($data));
    }

    public function listen(): void
    {
        $this->discord->listenCommand($this->commandName, function (In $interaction) {
            //        $user = $interaction->data->resolved->users->first();
            $this->permissions($interaction);
        });
    }

    private function permissions(In $interaction): void
    {
        if (isset($interaction->data->options['users'])) {
            foreach (explode(' ',
                str_replace('  ', ' ', $interaction->data->options['users']->value)) as $discord) {
                $id = str_replace('>', '', str_replace('<@', '', $discord));
                if ( !empty($id)) {
                    var_dump($id);
                }
            }
        }

        return;
        if ($interaction->member->permissions->use_application_commands) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("Gained Access For, {$interaction->user}"));
        } else {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("No Gain Access For, {$interaction->user}"));
        }
    }
}