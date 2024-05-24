<?php

namespace App\Helpers\Discord\Commands;

use App\Command;
use App\Helpers\Discord\DiscordCommand;
use App\Models\PendingPermission;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction as In;

class Permissions extends DiscordCommand implements Command
{

    public function __construct(Discord $discord, Discord $client)
    {
        parent::__construct($discord, $client, 'permissions', 'Add Permissions for DLPanel');

        $this->addOptions();
        $this->listen2(function (In $interaction) {
            $this->permissions($interaction);
        });
    }

    private function permissions(In $interaction): void
    {

        if ($interaction->member->roles->get('id', 1218998274791440415)) {
            $fields = [
                [
                    'name'  => 'Please Follow the instructions.',
                    'value' => ''
                ],
                [
                    'name'  => '1. Click the Access Button:',
                    'value' => 'Upon clicking the designated access button, you will be redirected to the panel login page.'
                ],
                [
                    'name'  => '2. Discord Authentication:',
                    'value' => 'To gain access, you must authenticate using your Discord account. Click the "Login with Discord" button and follow the prompts to log in securely.'
                ],
                [
                    'name'  => '3. Discord Authorization:',
                    'value' => 'After logging in, you\'ll be asked to authorize access to the panel from your Discord account. Confirm the authorization to proceed.'
                ],
                [
                    'name'  => '4. Wait for Approval:',
                    'value' => 'Once authorized, your access request will be sent to the owners/administrators for approval. Please be patient as they review and approve your request.'
                ],
                [
                    'name'  => '5. Notification of Approval:',
                    'value' => 'You will receive a notification via Discord once your access request has been approved. This notification will include instructions on how to proceed.'
                ],
                [
                    'name'  => '6. Access Granted:',
                    'value' => 'Upon approval, you will gain access to the panel and its functionalities. You can now proceed to use the panel as needed.'
                ],
                [
                    'name'  => 'Note:',
                    'value' => 'Access to the panel is exclusively granted through Discord authentication. Ensure you are logged in to Discord when accessing the panel, as no alternative login methods are available.'
                ]
            ];
            $embed = $this->embed($this->client, $fields, 'You\'ve got an invitation!');
            $message = $this->button(MessageBuilder::new()->addEmbed($embed), 'invitation',
                'Start Authorization');
            if (isset($interaction->data->options['users'])) {
                foreach (explode(' ',
                    str_replace('  ', ' ', $interaction->data->options['users']->value)) as $discord) {
                    $id = str_replace('>', '', str_replace('<@', '', $discord));
                    if ( !empty($id)) {
                        PendingPermission::updateOrCreate([
                            'discord_id' => $interaction->user->id,
                            'scopes'     => 'staff'
                        ]);
                        $interaction->user->sendMessage($message);
                    }
                }
            };

        } else {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("You Don't Have Any Permissions For That Use..\nThis Log Sent to the Owner."),
                true);
            $interaction->guild->owner->sendMessage(MessageBuilder::new()->setContent("<@{$interaction->user->id}> Tried To Use The Permissions Command"));
        }

        //        return;
        //        if ($interaction->member->permissions->use_application_commands) {
        //            $interaction->respondWithMessage(MessageBuilder::new()->setContent("Gained Access For, {$interaction->user}"));
        //        } else {
        //            $interaction->respondWithMessage(MessageBuilder::new()->setContent("No Gain Access For, {$interaction->user}"));
        //        }
    }

    public function addOptions(): void
    {
        $this->save($this->command
            ->addOption($this->addOption('users', 'Select Users To Add', Option::STRING))
            ->addOption($this->addOption('role', 'Select Role To Add', Option::ROLE))->toArray());
    }

}