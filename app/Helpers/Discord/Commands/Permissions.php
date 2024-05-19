<?php

namespace App\Helpers\Discord\Commands;

use App\Helpers\Discord\Client;
use App\Helpers\Discord\DiscordCommand;
use App\Models\GangCreationRequest;
use App\Models\Webhook;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction as In;

class Permissions extends DiscordCommand
{

    public function __construct(Discord $discord, Discord $client)
    {
        parent::__construct($discord, $client,'permissions', 'Add Permissions for DLPanel');

        $this->addOptions();
        $this->listen2(function (In $interaction) {
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

    private function addOptions(): void
    {
        $this->save($this->command
            ->addOption($this->addOption('users', 'Select Users To Add', Option::STRING))
            ->addOption($this->addOption('role', 'Select Role To Add', Option::ROLE))->toArray());
    }

}