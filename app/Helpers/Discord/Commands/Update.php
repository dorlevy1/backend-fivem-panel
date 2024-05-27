<?php

namespace App\Helpers\Discord\Commands;

use App\Command;
use App\Enums\Interaction as InteractionEnum;
use App\Helpers\Discord\DiscordCommand;
use App\Models\GangCreationRequest;
use App\Models\Webhook;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction as In;

class Update extends DiscordCommand implements Command
{

    public function __construct(Discord $discord, Discord $client)
    {
        parent::__construct($discord, $client, 'update', 'Update');

        $this->listen2(function (In $interaction) {
            $this->update($interaction);
        });
    }

    public function addOptions()
    {
    }

    public function interaction($name, $interaction)
    {

    }
    public function update(In $interaction): void
    {
        $gangCreateArea = Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id;
        $joinToGang = Webhook::where('name', '=', 'join-to-gang')->first()->channel_id;

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
        $embed = $this->createSummaryRequestEmbed($this->client, $interaction, $text, $roleExists, $talkTo);

    }

}