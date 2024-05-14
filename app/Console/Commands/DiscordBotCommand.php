<?php

namespace App\Console\Commands;

use App\Helpers\Discord\DiscordBot;
use App\Helpers\Discord\DiscordCommand;
use App\Helpers\Discord\Interaction;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord as DiscordPHP;
use Discord\Exceptions\IntentException;
use Discord\Parts\Interactions\Interaction as In;
use Discord\Parts\Interactions\Command\Option;
use Discord\WebSockets\Event;
use Illuminate\Console\Command;

class DiscordBotCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:discord-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Discord Bot';


    /**
     * @throws IntentException
     */
    function readyOn(DiscordPHP $d)
    {
        //        global $discord;
        //    $discord->sendEmbed('invitation');
        //    $discord->sendInvitation('invitation');

        $permissionCommand = new DiscordCommand($d, 'permissions', 'Add Permissions for DLPanel');

        $permissionCommand->save($permissionCommand->command
            ->addOption($permissionCommand->addOption('users', 'Select Users To Add', Option::STRING))
            ->addOption($permissionCommand->addOption('role', 'Select Role To Add', Option::ROLE))->toArray());

        $permissionCommand->listen();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

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
        //        $client->createButtonMessage(['id' => 'invitation', 'label' => 'Start Authorization']);
        //        $client->createEmbed(
        //            [
        //                'id'          => 'invitation',
        //                'title'       => 'DDL Panel',
        //                'description' => 'You\'ve got an invitation!',
        //                'fields'      => $fields
        //            ]);


        $client = new DiscordBot();

        $client->bot->on('ready', function (DiscordPHP $d) {
            $this->readyOn($d);

            $d->on(Event::INTERACTION_CREATE, function ($interaction, DiscordPHP $discord) {
                new Interaction($interaction);
            });
        });

        $client->run();
    }
}
