<?php

namespace Bot;

use Discord\Discord;
use Discord\Interaction;
use Discord\WebSockets\Event;

include '../vendor/autoload.php';
require_once 'DiscordBot.php';

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


$discord = new DiscordBot();
$discord->createButtonMessage(['id' => 'invitation', 'label' => 'Start Authorization']);
$discord->createEmbed(
    [
        'id'          => 'invitation',
        'title'       => 'DDL Panel',
        'description' => 'You\'ve got an invitation!',
        'fields'      => $fields
    ]);

function ready(Discord $d)
{
    global $discord;
    $discord->sendEmbed('invitation');
    $discord->sendInvitation('invitation');
}

//$discord->bot->on(Event::INTERACTION_CREATE, function (Message $message, Discord $discord) {
//    foreach ($message['components'][0]['components'] as $button) {
//        if ($button['custom_id'] === 'invitation') {
//            redirect('https://google.com');
//        }
//    }
//});


$discord->bot->on('ready', function (Discord $d) use ($discord) {
    ready($d);

    $d->on(Event::INTERACTION_CREATE, function ($interaction, Discord $discord) {
        if ($interaction['data']['custom_id'] === 'invitation') {
        }
    });
});

$discord->run();