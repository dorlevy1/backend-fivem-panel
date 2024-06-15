<?php

namespace App\Helpers\Discord;

use AllowDynamicProperties;
use App\Models\GangCreationRequest;
use App\Models\Webhook;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Discord as DiscordPHP;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction as In;

#[AllowDynamicProperties] class DiscordCommand extends DiscordMessage
{

    public CommandBuilder $command;
    public string $commandName;

    public function __construct(DiscordPHP $discord, DiscordPHP $client, $commandName, $commandDescription)
    {
        parent::__construct($client);
        $this->commandName = $commandName;
        $this->s = $client;
        $this->discord = $discord;
        $this->command = $this->create($commandName, $commandDescription);

    }

    private function create($commandName, $commandDescription): CommandBuilder
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

    /**
     * @throws \Exception
     */
    public function save($data): void
    {
        $this->discord->application->commands->save($this->discord->application->commands->create($data));
    }


    protected function listen2($cb): void
    {
        $this->discord->listenCommand($this->commandName, function (In $interaction) use ($cb) {
            $cb($interaction);
        });
    }
}