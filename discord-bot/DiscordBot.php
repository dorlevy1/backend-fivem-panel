<?php


namespace Bot;

include '../vendor/autoload.php';

use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Option;
use Discord\Interaction;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Dotenv\Dotenv;

class DiscordBot
{

    /**
     * @throws IntentException
     */

    private Dotenv $dotenv;

    public Discord $bot;

    public array $listeners;
    public array $actions;
    public array $buttons;
    public array $embeds;
    public array $builders;
    public array $data;


    /**
     * @throws IntentException
     */
    public function __construct()
    {
        $this->dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $this->dotenv->load();

        $this->bot = new Discord([
            'token'          => $_ENV['DISCORD_BOT_TOKEN'],
            'loadAllMembers' => true,
            'storeMessages'  => true,
            'retrieveBans'   => true,
            'pmChannels'     => true,
            'intents'        => Intents::getDefaultIntents() | Intents::GUILDS |
                Intents::GUILD_MESSAGES |
                Intents::DIRECT_MESSAGES |
                Intents::GUILD_MEMBERS |
                Intents::GUILD_MESSAGE_REACTIONS |
                Intents::MESSAGE_CONTENT
        ]);

        $this->data = [];

        $this->listeners = [];
        $this->buttons = [];
        $this->embeds = [];
        $this->actions = [];
        $this->builders = [];
    }

    //    public function onReady(): void
    //    {
    //        $this->bot->on('ready', function (Discord $discord) {
    //            $this->setData($discord);
    //
    //
    //            foreach ($this->embeds as $key => $embed) {
    //                $this->sendEmbed($key);
    //            }
    //
    //            foreach ($this->buttons as $key => $button) {
    //                $this->sendInvitation($key);
    //            }
    //
    //        });
    //    }


    public function createAction()
    {

    }

    public function sendInvitation($id): void
    {
        $guild = $this->bot->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);
        $guild->members->get('id',
            '604330997630238726')->sendMessage($this->builders[$id]);
    }

    public function createEmbed($data)
    {

        $data = (object)$data;
        $embed = $this->bot->factory(Embed::class);
        $embed->setTitle($data->title)
              ->setDescription($data->description)
              ->setAuthor('Invitation For DLPanel',
                  'https://cdn.discordapp.com/attachments/1236147966390046732/1236387837394288830/Screenshot_2024-05-04_at_21.43.40.png?ex=6637d367&is=663681e7&hm=7549a2544ea9a978a062b984f9889235a203b59b3ec8ece64840148762a05425&',
                  'https://discord.js.org')
              ->setThumbnail('https://cdn.discordapp.com/attachments/1236147966390046732/1236387576659841235/image.png?ex=6637d329&is=663681a9&hm=c2abe6e118a47548571b3f9110ee40f406995d087577ac07e6e7368c1736fe0d&')
              ->setFooter('DLPanel By D.D.L')->setTimestamp();


        foreach ($data->fields as $field) {
            $embed->addField($field);
        }

        $this->embeds[$data->id] = $embed;
    }


    public function sendEmbed($id)
    {
        $guild = $this->bot->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

        $guild->members->get('id',
            '604330997630238726')->sendMessage(MessageBuilder::new()->setEmbeds([$this->embeds[$id]]));
    }

    public function createButtonMessage($data): void
    {
        $data = (object)$data;
        $builder = MessageBuilder::new();

        $action = ActionRow::new();
        $button = Button::new(Button::STYLE_PRIMARY);
        $button->setLabel($data->label);
        isset($data->emoji) && $button->setEmoji($data->emoji);
        $action->addComponent($button);
        $builder->addComponent($action);

        $this->buttons[$data->id] = $button;
        $this->actions[$data->id] = $action;
        $this->builders[$data->id] = $builder;

        $this->setButtonListener($button, $builder);
    }

    public function setButtonListener($button, $builder): void
    {

        $button->setListener(function (Interaction $interaction) use ($button, $builder) {
            $button->setDisabled(true);
            $interaction->updateMessage($builder);
            $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent("{$interaction->user} המלשין סמוי פקח לחץ על הכפתוררר!"),
                true);
        }, $this->bot);
    }

    public function setData($discord): void
    {
        $guild = $discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);
        $channel = $guild->channels->get('id',
            '1236135756486017075');


        //        $this->createButtonMessage(['id' => 'invitation', 'label' => 'Start Authorization']);

        //        $this->createEmbed($discord,
        //            [
        //                'id'          => 'invitation',
        //                'title'       => 'DDL Panel',
        //                'description' => 'You\'ve got an invitation!',
        //                'fields'      => $fields
        //            ]);


        $addPermission = CommandBuilder::new()->setName('permissions')->setDescription('Add Permissions for DLPanel');
        $discord->application->commands->save($discord->application->commands->create(
            $addPermission->addOption((new Option($discord))->setName('user')
                                                            ->setDescription('Select User To Add')
                                                            ->setType(Option::USER))
                          ->addOption((new Option($discord))->setName('role')
                                                            ->setDescription('Select Role To Add')
                                                            ->setType(Option::ROLE))->toArray()

        ));


        $discord->listenCommand('permissions', function (Interaction $interaction) {
            //        $user = $interaction->data->resolved->users->first();
            if ($interaction->member->permissions->use_application_commands) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent("Gained Access For, {$interaction->user}"));
            } else {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent("No Gain Access For, {$interaction->user}"));
            }
        });


        //    $discord->guilds->get('id', '1192226332759834834')->channels->get('name',
        //        'dor-tests')->sendMessage($builder->addComponent($action->addComponent($button)))->then(function (
        //        Message $message
        //    ) {
        //        echo "Message sent in the channel {$message->channel->id}";
        //    });


        //    var_dump($discord->guilds->get('id', '1192226332759834834')->members->get('id',
        //        '604330997630238726')->sendMessage('Dor Test '));
        //    roles->get('name', 'Dev Access')
        //    $discord->users->fetch('604330997630238726', true)->then(function ($user) {
        //        var_dump($user);
        //    });

        // Listen for messages.
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            echo "{$message->author->username}: {$message->content}", PHP_EOL;
            // Note: MESSAGE_CONTENT intent must be enabled to get the content if the bot is not mentioned/DMed.
        });
    }


    public function run(): void
    {
        $this->bot->run();
    }

}