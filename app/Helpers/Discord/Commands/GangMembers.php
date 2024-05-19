<?php

namespace App\Helpers\Discord\Commands;

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

class GangMembers extends DiscordCommand
{

    public Discord $client;

    public function __construct(Discord $discord, Discord $client)
    {
        parent::__construct($discord, $client, 'gangmembers', 'Add Gang Members');

        $this->s = $client;
        $this->addOptions();
        $this->listen2(function (In $interaction) {
            $this->addGang($interaction);
        });
    }

    private function addGang(In $interaction): bool
    {
        $text = "";
        $readyForRequest = true;
        $talkTo = '';
        $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);


        $request = GangCreationRequest::where("discord_id", '=', $interaction->user->id)->first();

        if (isset($request->channel_id) && $interaction->guild->channels->get('id', $request->channel_id)) {
            $builder = $this->messageSummaryRequest($interaction);
            $interaction->guild->channels->get('id', $request->channel_id);
            $interaction->respondWithMessage($builder->setContent("You Already Have Exists Request.\n<#{$request->channel_id}>"),
                true);

            return false;
        }

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
                });
            });

        })->done();

        return true;
    }

    private function addOptions(): void
    {

        $this->save($this->command
            ->addOption($this->addOption('boss', 'Boss', Option::USER)->setRequired(true))
            ->addOption($this->addOption('co_boss', 'Co Boss', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-3', 'Member No 3', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-4', 'Member No 4', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-5', 'Member No 5', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-6', 'Member No 6', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-7', 'Member No 7', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-8', 'Member No 8', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-9', 'Member No 9', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-10', 'Member No 10', Option::USER)->setRequired(true))
            ->addOption($this->addOption('member-11', 'Member No 11', Option::USER))
            ->addOption($this->addOption('member-12', 'Member No 12', Option::USER))
            ->addOption($this->addOption('member-13', 'Member No 13', Option::USER))
            ->addOption($this->addOption('member-14', 'Member No 14', Option::USER))
            ->addOption($this->addOption('member-15', 'Member No 15', Option::USER))
            ->toArray());
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