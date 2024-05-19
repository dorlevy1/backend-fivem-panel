<?php

namespace App;

use App\Helpers\Discord\Discord;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction as In;

trait Command
{

    private Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    public function create($commandName, $commandDescription): CommandBuilder
    {
        return CommandBuilder::new()->setName($commandName)->setDescription($commandDescription);
    }

    public function addOption($name, $description, $type)
    {
        return (new Option($this->discord->client))
            ->setName($name)
            ->setDescription($description)
            ->setType($type);

    }

    public function save($data)
    {
        $this->discord->client->application->commands->save($this->discord->client->application->commands->create($data));
    }

    public function listen($cb): void
    {
        $this->discord->client->listenCommand($this->commandName, function (In $interaction) use ($cb) {
            return $cb($interaction);
        });
    }
}
