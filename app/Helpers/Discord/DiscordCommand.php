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

    public function save($data)
    {
        $this->discord->application->commands->save($this->discord->application->commands->create($data));
    }

    public function listen(): void
    {
        $this->discord->listenCommand($this->commandName, function (In $interaction) {
            //        $user = $interaction->data->resolved->users->first();

            match ($this->commandName) {
                'permissions' => $this->permissions($interaction),
                'gangmembers' => $this->addGang($interaction),
                'update' => $this->update($interaction),
            };
        });
    }

    protected function listen2($cb): void
    {
        $this->discord->listenCommand($this->commandName, function (In $interaction) use ($cb) {
            $cb($interaction);
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

    /**
     * @throws \Exception
     */
    private function addGang(In $interaction): void
    {
        $text = "";
        $readyForRequest = true;
        $talkTo = '';
        $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

        foreach ($interaction->data->options as $option) {
            $roles = $guild->members->get('id', $option->value)->roles;
            $roleExists = false;
            if (array_key_exists(1192227507508871349, $roles->toArray())) {
                $roleExists = true;
            } else {
                $readyForRequest = false;
                $talkTo .= "<@$option->value> ";
            }
            $exists = $roleExists ? ' ' . '✅' : ' ' . '❌';
            $ucfirst = ucfirst($option->name);
            $text .= "{$ucfirst} -  <@{$option->value}> {$exists}\n\n";
        }
        $boss = $interaction->data->options['boss']->value;
        $co_boss = $interaction->data->options['co_boss']->value;
        $options = $interaction->data->options;
        unset($options['boss']);
        unset($options['co_boss']);
        $arr = [];
        foreach ($options as $option) {
            $arr[] = $option->value;
        }


        $embed = $this->createSummaryRequestEmbed($this->s, $interaction, $text, $readyForRequest, $talkTo);

        $name = GangCreationRequest::where('discord_id', '=',
            $interaction->user->id)->first()->gang_name;
        $status = $readyForRequest ? '🟢' : '🟠';

        $pm = $guild->channels->create([
            'name'      => " {$interaction->user->displayname} - {$name}{$status}",
            'type'      => Channel::TYPE_GUILD_TEXT,
            'parent_id' => Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id,
            'nsfw'      => false,
        ]);


        $guild->channels->save($pm)->then(function (Channel $channel) use (
            $embed,
            $interaction,
            $guild,
            $talkTo,
            $arr,
            $co_boss,
            $boss,
            $readyForRequest
        ) {

            GangCreationRequest::updateOrCreate(['discord_id' => $interaction->user->id], [
                'members'           => implode(',', $arr),
                'co_boss'           => $co_boss,
                'boss'              => $boss,
                'ready_for_approve' => $readyForRequest,
                'channel_id'        => $channel->id,

            ]);
            $channel->setPermissions($interaction->guild->roles->get('name', '@everyone'),
                [], ['view_channel'])->done(function () use ($interaction, $channel, $embed, $guild, $talkTo) {
                $roles = empty($talkTo) ?
                    [['view_channel', 'send_messages', 'attach_files', 'add_reactions'], []]
                    : [['view_channel'], ['send_messages', 'attach_files', 'add_reactions']];

                $channel->setPermissions($guild->members->get('id', $interaction->user->id), ...$roles)->done(function (
                ) use (
                    $channel,
                    $embed,
                    $interaction,
                    $talkTo

                ) {
                    $builder = MessageBuilder::new()->addEmbed($embed);

                    if ( !empty($talkTo)) {
                        $action = ActionRow::new();
                        $button = Button::new(Button::STYLE_PRIMARY)->setCustomId('check_update_roles');
                        $button->setLabel('Check Updates Roles For Members');
                        $action->addComponent($button);
                        $builder->addComponent($action);
                    }

                    $channel->sendMessage($builder)->done();

                    $content = empty($talkTo) ? "<#$channel->id> Has Created Successfully." : "<#$channel->id> Has Created Successfully.\n**Please Notice!**\nYou have one or more members that\ndoes not have Allowlist Role.\nTalk to {$talkTo} soon as possible and update the Request\n\nAfter those members get the Allowlist Role.\nClick on the button in <#$channel->id>";

                    $interaction->respondWithMessage(MessageBuilder::new()->setContent($content),
                        true);
                }, function () {
                });
            });

        })->done();

    }

    public function update(In $interaction): void
    {
        $gangCreateArea = Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id;
        $joinToGang = Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id;

        match ($interaction->channel->parent_id) {
            $gangCreateArea => $this->updateRequestGang($interaction),
            $joinToGang => $interaction->respondWithMessage(MessageBuilder::new()->setContent("Not Here Mate..\nInside Your Request ONLY"))
        };
    }

    private function updateRequestGang(In $interaction)
    {
        $request = GangCreationRequest::where('discord_id', '=', $interaction->user->id)->first();

        if ( !$request) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("You Need To Create Request First."),
                true);

            return false;
        }
        if (is_null($request->members)) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("You Have not insert any members to your Gang request\n Please add with \gangmembers"),
                true);

            return false;
        }

        $roleExists = false;
        $talkTo = '';
        $text = '';
        $rolesBoss = $interaction->guild->members->get('id', $request->boss)->roles;
        $rolesCo = $interaction->guild->members->get('id', $request->co_boss)->roles;
        $exists = array_key_exists(1192227507508871349, $rolesBoss->toArray()) ? ' ' . '✅' : ' ' . '❌';
        $existsCo = array_key_exists(1192227507508871349, $rolesCo->toArray()) ? ' ' . '✅' : ' ' . '❌';

        $text .= "Boss -  <@{$request->boss}> {$exists}\n\n";
        $text .= "Boss -  <@{$request->co_boss}> {$existsCo}\n\n";

        foreach (explode(',', $request->members) as $key => $member) {
            $roles = $interaction->guild->members->get('id', $member)->roles;
            if (array_key_exists(1192227507508871349, $roles->toArray())) {
                $roleExists = true;
            } else {
                $roleExists = false;
                $talkTo .= "<@{$member}> ";
            }
            $exists = $roleExists ? ' ' . '✅' : ' ' . '❌';
            $key = $key + 1;
            $text .= "Member No.{$key} -  <@{$member}> {$exists}\n\n";
        }
        $embed = $this->createSummaryRequestEmbed($this->s, $interaction, $text, $roleExists, $talkTo);

    }


}