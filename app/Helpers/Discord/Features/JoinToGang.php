<?php

namespace App\Helpers\Discord\Features;

use App\Enums\Interaction as InteractionEnum;
use App\Feature;
use App\Helpers\API;
use App\Helpers\Discord\DiscordMessage;
use App\Models\Criminal;
use App\Models\DiscordBot;
use App\Models\Gang;
use App\Models\GangCreationRequest;
use App\Models\Player;
use App\Models\Webhook;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\Option;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Discord as DiscordPHP;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Interaction as In;
use Illuminate\Support\Facades\DB;


class JoinToGang extends DiscordMessage implements Feature
{

    public API $api;
    public DiscordMessage $message;
    public DiscordPHP $discord;
    public DiscordPHP $client;

    public function __construct(DiscordPHP $discord, DiscordPHP $client)
    {
        parent::__construct($discord);
        $this->api = new API();
        $this->discord = $discord;
        $this->client = $client;
        $this->message = new DiscordMessage($client);
        $this->handle();
    }


    /**
     * @throws \Exception
     */
    public function interaction($interaction)
    {
        $name = explode('+', $interaction->data->custom_id)[0];

        return match ($name) {
            InteractionEnum::GANG_REQUEST->value => $this->gang_request($interaction),
            InteractionEnum::CHECK_UPDATE_ROLES->value => $this->check_update_roles($interaction),
            InteractionEnum::APPROVE_GANG->value => $this->approve_gang($interaction),
            InteractionEnum::DECLINE_GANG->value => $this->decline_gang($interaction),
            InteractionEnum::SET_GANG_NAME->value => $this->set_gang_name($interaction),
            InteractionEnum::DELETE_CHANNEL->value => $this->delete_channel($interaction),
            default => true,
        };
    }

    private function gang_request(In $interaction)
    {

        $instructions = DiscordBot::category('Gang Area')->where('label', '=', 'Instructions')->first()->value;

        $fields = [];
        foreach ($instructions as $instruction) {
            $fields[] = ['name' => $instruction->name, 'value' => $instruction->value];
        }

        $select = StringSelect::new();
        foreach (DB::connection('second_db')->table('gangs_data')->where('available', '=', true)->get() as $gang) {
            $select->addOption(Option::new(ucfirst($gang->name) . ' - ' . $gang->color_name, $gang->name))
                ->setCustomId('set_gang_name+' . $gang->name);
        }

        $request = GangCreationRequest::where("discord_id", '=', $interaction->user->id)->first();

        if (isset($request->channel_id) && $interaction->guild->channels->get('id', $request->channel_id)) {
            $builder = $this->messageSummaryRequest($interaction);
            if (!$builder) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent("U Did not added your Boss Or Co Boss"));
                $interaction->acknowledge();
                return false;
            }
            $interaction->guild->channels->get('id', $request->channel_id);
            $interaction->respondWithMessage($builder->setContent("You Already Have Exists Request.\n<#{$request->channel_id}>"),
                true);

            return false;
        }

        $embed = $this->embed($this->client, $fields, 'Choose Gang');

        $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($select), true);
        $interaction->acknowledge();
        return true;
    }


    private function delete_channel(In $interaction): bool
    {
        $discord = explode('+', $interaction->data->custom_id)[1];

        if ($interaction->user->id !== $discord) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("You're not allowed to do that!"));
            $interaction->acknowledge();
            return false;
        }
        $interaction->channel->deleteChannel();
        $interaction->acknowledge();
        return true;

    }

    private function set_gang_name(In $interaction)
    {

        $name = $interaction->data->values[0];

        $fields = [
            ['name' => 'Chosen Gang', 'value' => $name],
            ['name' => 'Request By', 'value' => "<@{$interaction->user->id}>"],
        ];

        GangCreationRequest::updateOrCreate(['discord_id' => $interaction->user->id], [
            'gang_name' => $name,
            'boss' => null,
            'co_boss' => null,
            'members' => null,
            'channel_id' => null
        ]);


        $instructions = DiscordBot::category('Gang Area')->where('label', '=', 'Member Instructions')->first()->value;

        $actionToTake = [];
        foreach ($instructions as $instruction) {
            $actionToTake[] = ['name' => $instruction->name, 'value' => $instruction->value];
        }

        $embed = $this->embed($this->client, $fields, 'Request Begin ');
        $embed2 = $this->embed($this->client, $actionToTake, 'Add Your Gang Members!');
        $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed($embed), true);
        $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed($embed2), true);
        $interaction->acknowledge();
        return true;
    }

    /**
     * @throws \Exception
     */
    private function check_update_roles(In $interaction): bool
    {
        $discord_id = explode('+', $interaction->data->custom_id)[1];


        $builder = $this->messageSummaryRequest($interaction, $discord_id);
        if (!$builder) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("U Did not added your Boss Or Co Boss"));
            $interaction->acknowledge();
            return false;
        }
        $request = GangCreationRequest::where('discord_id', '=', $discord_id)->first();
        $talkTo = false;


        foreach (explode(',', $request->members) as $key => $member) {
            $roles = $interaction->guild->members->get('id', $member)->roles;
            $player = Player::all()->first(function ($player) use ($member) {
                return $player->discord === 'discord:' . $member;
            });
            if (!array_key_exists(1192227507508871349, $roles->toArray()) || !$player) {
                $talkTo = true;
            }
        }

        $action = ActionRow::new();

        if ($talkTo) {
            $button = Button::new(Button::STYLE_PRIMARY)->setCustomId('check_update_roles+' . $discord_id);
            $button->setLabel('Check Updates Roles For Members');
            $action->addComponent($button);
            $builder->addComponent($action);
        }

        $name = $request->gang_name;
        $status = !$talkTo ? '🟢' : '🟠';

        $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

        $roles = $talkTo ?
            [['view_channel', 'send_messages', 'attach_files', 'add_reactions'], []]
            : [['view_channel'], ['send_messages', 'attach_files', 'add_reactions']];
        $user = $guild->members->get('id', $discord_id);

        $interaction->channel->setPermissions($user, ...$roles);
        $interaction->channel->name = $user->displayname . '-' . $name . $status;
        $guild->channels->save($interaction->channel);
        $interaction->updateMessage($builder);
        $interaction->acknowledge();

//        $interaction->message->delete();
//        $interaction->channel->sendMessage($builder)->done();
        if (!$talkTo) {
            $button1 = Button::new(Button::STYLE_DANGER)->setCustomId('decline_gang+' . $discord_id);
            $button1->setLabel('Decline');
            $button2 = Button::new(Button::STYLE_SUCCESS)->setCustomId('approve_gang+' . $discord_id);
            $button2->setLabel('Approve');
            $action->addComponent($button1);
            $action->addComponent($button2);
            $builder->addComponent($action);
        }

        $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD_LOGS'));

        !$talkTo && $guild->channels->get('name', 'dlp-gang-requests')->sendMessage($builder);

        return true;
    }

    private function getDataRequest(In $interaction): object|false
    {
        if (!($interaction->member->roles->get('id', 1047571391496597527))) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("You Don't Have Any Permissions For That Use..\nThis Log Sent to the Owner."),
                true);
//            $interaction->guild->owner->sendMessage(MessageBuilder::new()->setContent("<@{$interaction->user->id}> Tried To Confirm Gang"));
            $interaction->acknowledge();
            return false;
        }

        $discord_request = explode('+', $interaction->data->custom_id)[1];
        $gangRequest = GangCreationRequest::where('discord_id', '=', $discord_request)->first();
        $gangData = DB::connection('second_db')->table('gangs_data')->where('name', '=',
            strtolower($gangRequest->gang_name))->first();

        return (object)['gangRequest' => $gangRequest, 'gangData' => $gangData];
    }

    /**
     * @throws \Exception
     */
    private function approve_gang(In $interaction): bool
    {

        $data = $this->getDataRequest($interaction);
        if ($data) {

            $gangData = $data->gangData;
            $gangRequest = $data->gangRequest;


            $gangMembers = explode(',', $gangRequest->members);
            $gangMembers[] = $gangRequest->boss;
            $gangMembers[] = $gangRequest->co_boss;

            foreach (Player::all() as $player) {
                foreach ($gangMembers as $member) {
                    if ($player->metadata->discord === 'discord:' . $gangRequest->boss) {
                        Gang::updateOrCreate(['name' => $gangData->name], [
                            'name' => $gangData->name,
                            'owner' => $player->citizenid,
                            'zones' => '[]',
                            'picture' => '',
                            'color' => "#{$gangData->color_hex}",
                        ]);
                    }
                    if ($player->metadata->discord === 'discord:' . $member) {
                        Criminal::where('identifier', '=',
                            $player->citizenid)->update(['organization' => $gangData->name]);
                    }
                }
            }


            $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

            $ch = $guild->channels->get('id', $gangRequest->channel_id);

            $fields = [
                ['name' => 'Approved By', 'value' => "<@{$interaction->user->id}>"]
            ];

            $embed = $this->embed($this->client, $fields, 'Action Approved');
            $member = $guild->members->get('id', $gangRequest->discord_id);
            $ch->name = $member->user->displayname . '-!Approved!🟢';
            $ch->setPermissions($guild->members->get('id', $interaction->user->id),
                ['view_channel', 'send_messages'])->done();
            $guild->channels->save($ch);


            return $this->sendAnsweredMessageGangRequest($gangRequest, $interaction, $embed, $ch);
        }
        return false;
    }

    private function decline_gang(In $interaction): bool
    {
        $data = $this->getDataRequest($interaction);
        if ($data) {

            $gangRequest = $data->gangRequest;

            $interaction->showModal('Add Reason', 'reason_modal',
                [
                    ActionRow::new()->addComponent(TextInput::new("Reason",
                        TextInput::STYLE_PARAGRAPH)->setCustomId('reason'))
                ],
                function (In $interaction, Collection $components) use ($gangRequest) {
                    $reason = $components['reason']['value'];

                    $fields = [
                        ['name' => 'Decline By', 'value' => "<@{$interaction->user->id}>"],
                        ['name' => '**Reason**', 'value' => $reason],
                    ];
                    $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

                    $ch = $guild->channels->get('id', $gangRequest->channel_id);

                    $embed = $this->embed($this->client, $fields, 'Action Decline');
                    $member = $guild->members->get('id', $gangRequest->discord_id);
                    $ch->name = $member->user->displayname . '-!Decline!🔴';
                    $ch->setPermissions($guild->members->get('id', $interaction->user->id),
                        ['view_channel'], ['send_messages'])->done();
                    $guild->channels->save($ch);

                    return $this->sendAnsweredMessageGangRequest($gangRequest, $interaction, $embed, $ch);
                });
            return true;
        }
        return false;
    }

    /**
     * @param $gangRequest
     * @param In $interaction
     * @param \Discord\Parts\Part|\Discord\Repository\AbstractRepository $embed
     * @param \Discord\Parts\Channel\Channel|null $ch
     *
     * @return true
     */
    private function sendAnsweredMessageGangRequest(
        $gangRequest,
        In $interaction,
        \Discord\Parts\Part|\Discord\Repository\AbstractRepository $embed,
        ?\Discord\Parts\Channel\Channel $ch
    ): bool
    {
        $interaction->user->id = $gangRequest->discord_id;
        $builder = $this->messageSummaryRequest($interaction);
        if (!$builder) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("U Did not added your Boss Or Co Boss"));
            $interaction->acknowledge();
            return false;
        }
        $ar = ActionRow::new();
        $submit = Button::new(Button::STYLE_PRIMARY, 'delete_channel+' . $gangRequest->discord_id)->setLabel('Delete Channel');
        $ar->addComponent($submit);
        $builder->addEmbed($embed);

        $ch->sendMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($ar));
        $interaction->updateMessage($builder);
//        $interaction->message->delete();
        $interaction->acknowledge();

        return true;
    }

    public function createButtonChannel(Guild $guild): bool|string
    {

        $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD_LOGS'));
        $guildMain = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));

        try {
            if (!is_null($guild->channels->get('name', 'dlp-gang-requests'))) {
                return false;
            }

            $embed = $this->message->embed($this->client, [], 'Gang Creation Area');
            $builder = MessageBuilder::new();
            $ar = ActionRow::new();
            $submit = Button::new(Button::STYLE_PRIMARY,
                'gang_request')->setLabel('Click To Apply Gang Request.');
            $ar->addComponent($submit);
            $builder->addEmbed($embed);
            $builder->addComponent($ar);
            $guildMain->channels->get('name', 'join-to-gang')->sendMessage($builder);

            return true;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

    public function createMainChannel(Guild $guild): void
    {

        if (!is_null($guild->channels->get('name', 'join-to-gang'))) {
            Webhook::updateOrCreate([
                'name' => 'join-to-gang'
            ], [
                'name' => $guild->channels->get('name', 'join-to-gang')->name,
                'channel_id' => $guild->channels->get('name', 'join-to-gang')->id,
                'parent' => false
            ]);

            return;
        }

        $category = Webhook::where('name', '=', 'Gang Create Area')->first()->channel_id;

        $channel = $guild->channels->create([
            'name' => 'join-to-gang',
            'type' => Channel::TYPE_GUILD_TEXT,
            'parent_id' => $category,
            'nsfw' => false
        ]);

        $guild->channels->save($channel)->then(function (Channel $channel) {
            Webhook::updateOrCreate([
                'name' => $channel->name
            ], [
                'name' => $channel->name,
                'channel_id' => $channel->id,
                'parent' => false
            ]);

            return $channel;
        })->done();

    }

    public function handle(): void
    {
        $this->createCat();
        $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
        $this->createMainChannel($guild);
        $this->createButtonChannel($guild);
        $this->createLogPage($guild);
    }

    public function createCat(): Guild|string
    {
        try {
            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
            if (!is_null($guild->channels->get('name', 'Gang Create Area'))) {

                Webhook::updateOrCreate([
                    'name' => 'Gang Create Area'
                ], [
                    'name' => $guild->channels->get('name', 'Gang Create Area')->name,
                    'channel_id' => $guild->channels->get('name', 'Gang Create Area')->id,
                    'parent' => true
                ]);

                return false;
            }
            $group = $guild->channels->create([
                'name' => 'Gang Create Area',
                'type' => Channel::TYPE_GUILD_CATEGORY,
            ]);

            $guild->channels->save($group)->then(function (Channel $channel) use ($guild) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name' => $channel->name,
                    'channel_id' => $channel->id,
                    'parent' => true
                ]);

                return $guild;
            })->done();

            return $guild;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }

    public function createLogPage(Guild $guild): void
    {
        try {

            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD_LOGS'));


            if (!is_null($guild->channels->get('name', 'dlp-gang-requests'))) {
                Webhook::updateOrCreate([
                    'name' => 'dlp-gang-requests'
                ], [
                    'name' => $guild->channels->get('name', 'dlp-gang-requests')->name,
                    'channel_id' => $guild->channels->get('name', 'dlp-gang-requests')->id,
                    'parent' => false
                ]);

                return;
            }

            $category = Webhook::where('name', '=', 'DLPanel')->first()->channel_id;
            $channel = $guild->channels->create([
                'name' => 'dlp-gang-requests',
                'type' => Channel::TYPE_GUILD_TEXT,
                'parent_id' => $category,
                'nsfw' => false
            ]);


            $guild->channels->save($channel)->then(function (Channel $channel) use ($guild) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name' => $channel->name,
                    'channel_id' => $channel->id,
                    'parent' => false
                ]);

                $channel->setPermissions($guild->roles->get('name', '@everyone'),
                    [], ['view_channel'])->done(function () use ($channel, $guild) {
                    $channel->setPermissions($guild->roles->get('id', '1218998274791440415'), ['view_channel'],
                        ['send_messages', 'attach_files', 'add_reactions'])->done();
                });
            })->done();

            return;
        } catch (\ErrorException $e) {
            return;
        }
    }

}
