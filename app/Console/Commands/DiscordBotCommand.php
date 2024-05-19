<?php

namespace App\Console\Commands;

use App\Helpers\Discord\Commands\GangMembers;
use App\Helpers\Discord\Commands\Permissions;
use App\Helpers\Discord\Commands\Update;
use App\Helpers\Discord\Discord;
use App\Helpers\Discord\DiscordBot;
use App\Helpers\Discord\DiscordCommand;
use App\Helpers\Discord\Features\CreateGangButton;
use App\Helpers\Discord\Interaction;
use Discord\Discord as DiscordPHP;
use Discord\Exceptions\IntentException;
use Discord\Parts\Channel\Message;
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
    function readyOn(DiscordPHP $d, Discord $client)
    {
        //        global $discord;
        //    $discord->sendEmbed('invitation');
        //    $discord->sendInvitation('invitation');

        $permissionCommand = new DiscordCommand($d, $client, 'permissions', 'Add Permissions for DLPanel');

        $permissionCommand->save($permissionCommand->command
            ->addOption($permissionCommand->addOption('users', 'Select Users To Add', Option::STRING))
            ->addOption($permissionCommand->addOption('role', 'Select Role To Add', Option::ROLE))->toArray());

        $permissionCommand->listen();

        $addGangCommand = new DiscordCommand($d, $client, 'gangmembers', 'Add Gang Members');

        $addGangCommand->save($addGangCommand->command
            ->addOption($addGangCommand->addOption('boss', 'Boss', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('co_boss', 'Co Boss', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-3', 'Member No 3', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-4', 'Member No 4', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-5', 'Member No 5', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-6', 'Member No 6', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-7', 'Member No 7', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-8', 'Member No 8', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-9', 'Member No 9', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-10', 'Member No 10', Option::USER)->setRequired(true))
            ->addOption($addGangCommand->addOption('member-11', 'Member No 11', Option::USER))
            ->addOption($addGangCommand->addOption('member-12', 'Member No 12', Option::USER))
            ->addOption($addGangCommand->addOption('member-13', 'Member No 13', Option::USER))
            ->addOption($addGangCommand->addOption('member-14', 'Member No 14', Option::USER))
            ->addOption($addGangCommand->addOption('member-15', 'Member No 15', Option::USER))
            ->toArray());

        $addGangCommand->listen();

        $update = new DiscordCommand($d, $client, 'update', 'Update');

        $update->save($update->command->toArray());
        $update->listen();

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


        new Discord();

        //        $discord->client->on('init', function (DiscordPHP $d) use ($discord) {
        //            new CreateGangButton($d, $discord);
        ////            $this->readyOn($d, $discord);
        //            $d->on(Event::MESSAGE_CREATE, function (Message $message, DiscordPHP $discord) {
        //                echo "{$message->author->username}: {$message->content}", PHP_EOL;
        //            });
        //
        //            $d->on(Event::INTERACTION_CREATE, function ($interaction, DiscordPHP $discordPHP) use ($discord) {
        //                new Interaction($discord, $discordPHP, $interaction);
        //            });
        //        });

    }
}
