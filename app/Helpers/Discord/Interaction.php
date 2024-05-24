<?php

namespace App\Helpers\Discord;

use App\Helpers\API;
use App\Models\Admin;
use App\Models\Ban;
use App\Enums\Interaction as InteractionEnum;
use App\Models\Criminal;
use App\Models\Gang;
use App\Models\GangCreationRequest;
use App\Models\Player;
use App\Models\Webhook;
use Carbon\Carbon;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\Option;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Illuminate\Http\Request;
use Discord\Parts\Interactions\Interaction as In;
use Discord\Discord as DiscordPHP;
use Illuminate\Support\Facades\DB;

class Interaction extends DiscordMessage
{

    /**
     * @throws \Exception
     */
    public function __construct(DiscordPHP $client, DiscordPHP $discord, In $interaction)
    {
        parent::__construct($client);
        $name = explode('+', $interaction->data->custom_id)[0];

        $this->discord = $discord;
        $this->client = $client;

        return match ($name) {
            InteractionEnum::CANCEL_BAN->value => $this->cancel_ban($interaction),
            InteractionEnum::ADD_BAN->value => $this->add_ban($interaction),
            InteractionEnum::GANG_REQUEST->value => $this->gang_request($interaction),
            InteractionEnum::CHECK_UPDATE_ROLES->value => $this->check_update_roles($interaction),
            InteractionEnum::APPROVE_GANG->value => $this->approve_gang($interaction),
            default => true,
        };

    }

    public function removeMessage($channel_id, $message_id)
    {
        try {
            $endpoint = \App\Enums\Discord::DELETE_MESSAGE->endpoint([
                'channelId' => $channel_id,
                'messageId' => $message_id
            ]);

            $data = (new API())->apiRequest("{$endpoint}", null,
                env('DISCORD_BOT_TOKEN'), 'Bot', true, 'DELETE');

            return $data->id;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
    }


    private function removeBan($reply, $playerDiscordId, $adminDiscordId, $ban): void
    {
        $time = date('Y-m-d h:i:s', $ban->expire);
        $timenoew = date('Y-m-d h:i:s');
        $fields = [
            [
                'name'  => 'Ban Removal Details',
                'value' => "**Action By:** <@{$adminDiscordId}>\n**Player:** <@{$playerDiscordId}>\n**Ban Until:** ||{$time}||\n**Reason:** ||{$ban->reason}||\n**Cancellation Date:** ||{$timenoew}||"
            ],
        ];

        $reply && $this->removeMessage(Webhook::where('name', '=', 'bans')->first()->channel_id,
            $reply);
        $this->message([
            'playerDiscordId' => $playerDiscordId,
            'adminDiscordId'  => $adminDiscordId,
            'fields'          => $fields
        ]);
        $ban->delete();

    }


    private function getPermissions(In $interaction): bool
    {
        foreach ($interaction->member->roles as $role) {
            if (strtolower($role->name) === 'bans' || strtolower($role->name) === 'god') {
                $admin = Admin::where('discord_id', '=', $interaction['member']['user']->id)->first();
                if ( !is_null($admin)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function cancel_ban(In $interaction): bool
    {
        $discord_id_ban = explode('+', $interaction->data->custom_id)[1];
        if ($this->getPermissions($interaction)) {
            $ban = Ban::where('discord', 'LIKE', '%' . $discord_id_ban . '%')->first();
            if (is_null($ban)) {
                $this->createMessage([
                    'adminDiscordId' => $interaction['member']['user']->id,
                    'title'          => "No Access Removed Ban",
                    'description'    => "<@{$interaction['member']['user']->id}> Tried To Remove Ban Which Not Exists",
                    'webhook'        => 'bans',
                    'reply'          => $interaction['message']['id']
                ]);
                $interaction->acknowledge();

                return false;
            }
            $this->removeBan($interaction['message']['id'], $discord_id_ban,
                $interaction['member']['user']->id, $ban);
            $interaction->acknowledge();

            return true;
        } else {
            $this->createMessage([
                'adminDiscordId' => $interaction['member']['user']->id,
                'title'          => "No Access For Bans",
                'description'    => "<@{$interaction['member']['user']->id}> Tried To Remove Ban For <@{$discord_id_ban}>",
                'webhook'        => 'bans',
                'reply'          => $interaction['message']['id']
            ]);
            $interaction->acknowledge();

            return false;
        }
    }

    private function add_ban($interaction)
    {
        $ar = ActionRow::new();
        $ti = TextInput::new('Add Ban In Days', TextInput::STYLE_SHORT, "{$interaction['data']['custom_id']}first");
        $ar->addComponent($ti);
        $discord_id_ban = explode('+', $interaction['data']['custom_id'])[1];
        $reason = explode('+', $interaction['data']['custom_id'])[2];
        if ($this->getPermissions($interaction)) {
            $ban = Ban::where('discord', 'LIKE', '%' . $discord_id_ban . '%')->first();
            if ( !is_null($ban)) {
                $this->createMessage([
                    'adminDiscordId' => $interaction['member']['user']->id,
                    'title'          => "No Access Removed Ban",
                    'description'    => "<@{$interaction['member']['user']->id}> Tried To Remove Ban Which Already Exists\nTo <@{$discord_id_ban}>",
                    'webhook'        => 'bans',
                    //                    'reply'          => $interaction['message']['id']
                ]);
                $this->removeMessage(Webhook::where('name', '=', 'warns')->first()->channel_id,
                    $interaction['message']['id']);
                $interaction->acknowledge();

                return false;
            }

            $interaction->showModal('Give Ban To Player', $interaction['data']['custom_id'], [$ar],
                function (In $i, Collection $components) use ($discord_id_ban, $reason, $interaction) {
                    $dateUnbtil = Carbon::now()->addDays($components["{$interaction['data']['custom_id']}first"]['value']);
                    foreach (Player::all() as $player) {
                        if ($player->metadata->discord === 'discord:' . $discord_id_ban) {
                            Ban::create([
                                'discord'  => $player->metadata->discord,
                                'license'  => strval($player->license),
                                'name'     => $player->name,
                                'ip'       => (new Request())->ip(),
                                'reason'   => $reason,
                                'expire'   => $dateUnbtil->timestamp,
                                'bannedby' => $interaction['member']['user']['username']
                            ]);
                            $fields = [
                                [
                                    'name'  => 'Ban Details',
                                    'value' => "**Action By:** <@{$interaction['member']['user']->id}>\n**Player:** <@{$discord_id_ban}>\n**Ban Until:** ||{$dateUnbtil}||\n**Reason:** ||{$reason}||"
                                ],
                            ];
                            $components['components'] = [
                                [
                                    "type"       => 1,
                                    "components" => [
                                        [
                                            "type"      => 2,
                                            "label"     => "Click To Cancel Ban.",
                                            "style"     => 1,
                                            "custom_id" => "cancel_ban+" . $discord_id_ban
                                        ]
                                    ]
                                ],
                            ];

                            $this->createMessage([
                                'adminDiscordId' => $interaction['member']['user']->id,
                                'title'          => 'Ban Added',
                                'description'    => "<@{$interaction['member']['user']->id}> Give Ban To <@{$discord_id_ban}>!",
                                'webhook'        => "bans",
                                'fields'         => $fields,
                                'components'     => $components,

                            ]);
                            $this->removeMessage(Webhook::where('name', '=', 'warns')->first()->channel_id,
                                $interaction['message']['id']);
                            $i->respondWithMessage(MessageBuilder::new()->setContent("Ban Is Added"), true);
                            $i->acknowledge();

                            return true;
                        }
                    }
                    $i->acknowledge();

                    return true;
                });
        } else {
            $this->createMessage([
                'adminDiscordId' => $interaction['member']['user']->id,
                'title'          => "No Access For Bans",
                'description'    => "<@{$interaction['member']['user']->id}> Tried To Remove Ban For <@{$discord_id_ban}>",
                'webhook'        => 'bans',
                'reply'          => $interaction['message']['id']
            ]);
            $interaction->acknowledge();

            return false;
        }
    }


    private function gang_request(In $interaction)
    {

        $fields = [
            [
                'name'  => 'Gang Member Registration Form',
                'value' => 'Add a minimum of 10 and a maximum of 15 names of gang members. For each, write the full name, age, and Discord ID. Specify for each whether they are currently in the gang on the server or not, and whether they have a whitelist or not. Bosses and co-bosses over 16 are mandatory!'
            ],
            [
                'name'  => 'CID Submission Requirement',
                'value' => 'Please specify the CID of each player in this field. Without this, you will not receive access in the game, and tickets on the subject will not receive a response if the CID is not entered here.'
            ],
            [
                'name'  => 'Gang Selection and Customization',
                'value' => 'Choose from the available options the gang you desire. The chosen gang includes pre-defined color, neighborhood, status, etc., and the options before you are the currently available ones. For questions or extreme cases, please open a support ticket.'
            ],
            [
                'name'  => 'Approval Notification for Crime Server Access',
                'value' => 'If approved, you will be notified and receive invitations to the crime server, along with corresponding roles. If not approved, you will also be informed.'
            ]
        ];

        $select = StringSelect::new();
        foreach (DB::connection('second_db')->table('gangs_data')->where('available', '=', true)->get() as $gang) {
            $select->addOption(Option::new(ucfirst($gang->name), $gang->name . ' - ' . $gang->color_name))
                   ->setCustomId($gang->name);
        }

        $request = GangCreationRequest::where("discord_id", '=', $interaction->user->id)->first();

        if (isset($request->channel_id) && $interaction->guild->channels->get('id', $request->channel_id)) {
            $builder = $this->messageSummaryRequest($interaction);
            $interaction->guild->channels->get('id', $request->channel_id);
            $interaction->respondWithMessage($builder->setContent("You Already Have Exists Request.\n<#{$request->channel_id}>"),
                true);

            return false;
        }

        $embed = $this->embed($this->client, $fields, 'Choose Gang');

        $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($select), true);

        $select->setListener(function (In $interaction, Collection $options) {
            foreach ($options as $option) {
                $fields = [
                    ['name' => 'Chosen Gang', 'value' => $option->getValue()],
                    ['name' => 'Request By', 'value' => "<@{$interaction->user->id}>"],
                ];

                GangCreationRequest::updateOrCreate(['discord_id' => $interaction->user->id], [
                    'gang_name'  => $option->getLabel(),
                    'boss'       => null,
                    'co_boss'    => null,
                    'members'    => null,
                    'channel_id' => null
                ]);

                $actionToTake = [
                    [
                        'name'  => 'Command Usage',
                        'value' => 'Please use the following command to register gang members:'
                    ],
                    [
                        'name'  => 'Command Syntax',
                        'value' => '/gangmembers [boss_id] [co_boss_id] [member-1] [member-2] ... [member-10]'
                    ],
                    [
                        'name'  => 'Replace [boss]',
                        'value' => 'Replace [boss] with the Discord Tag of the gang boss.'
                    ],
                    [
                        'name'  => 'Replace [co_boss]',
                        'value' => 'Replace [co_boss] with the Discord Tag of the co-boss.'
                    ],
                    [
                        'name'  => 'Replace [member-1] to [member-10]',
                        'value' => 'Replace [member-1] to [member-10] with the Discord Tags of the remaining gang members. Include at least 10 Tags and up to a maximum of 15 Tags.'
                    ],
                    ['name' => 'Separation', 'value' => 'Ensure each ID is separated by a space.'],
                    [
                        'name'  => 'Execution',
                        'value' => 'Once all IDs are correctly entered, execute the command to register the gang members.'
                    ]
                ];
                $embed = $this->embed($this->client, $fields, 'Request Begin ');
                $embed2 = $this->embed($this->client, $actionToTake, 'Add You Gang Members!');
                $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed($embed), true);
                $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed($embed2), true);
                $interaction->acknowledge();
            }
        }, $this->discord);
    }

    /**
     * @throws \Exception
     */
    private function check_update_roles(In $interaction): bool
    {
        $builder = $this->messageSummaryRequest($interaction);
        $request = GangCreationRequest::where('discord_id', '=',
            $interaction->user->id)->first();
        $talkTo = false;

        foreach (explode(',', $request->members) as $key => $member) {
            $roles = $interaction->guild->members->get('id', $member)->roles;
            if ( !array_key_exists(1192227507508871349, $roles->toArray())) {
                $talkTo = true;
            }
        }

        $action = ActionRow::new();

        if ($talkTo) {
            $button = Button::new(Button::STYLE_PRIMARY)->setCustomId('check_update_roles');
            $button->setLabel('Check Updates Roles For Members');
            $action->addComponent($button);
            $builder->addComponent($action);
        }

        $name = $request->gang_name;
        $status = !$talkTo ? '🟢' : '🟠';

        $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

        $interaction->channel->setPermissions($guild->members->get('id', $interaction->user->id),
            ['view_channel', 'send_messages', 'attach_files', 'add_reactions'])->done();
        $interaction->channel->name = $interaction->user->displayname . '-' . $name . $status;
        $guild->channels->save($interaction->channel);
        $interaction->message->delete();
        $interaction->channel->sendMessage($builder)->done();

        if ( !$talkTo) {
            $button1 = Button::new(Button::STYLE_SUCCESS)->setCustomId('decline_gang+' . $interaction->user->id);
            $button1->setLabel('Decline');
            $button2 = Button::new(Button::STYLE_DANGER)->setCustomId('approve_gang+' . $interaction->user->id);
            $button2->setLabel('Approve');
            $action->addComponent($button1);
            $action->addComponent($button2);
            $builder->addComponent($action);
        }

        !$talkTo && $guild->channels->get('name', 'gang-requests')->sendMessage($builder);

        $interaction->acknowledge();

        return true;
    }

    private function approve_gang(In $interaction): bool
    {

        if ( !($interaction->member->roles->get('id', 1218998274791440415))) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("You Don't Have Any Permissions For That Use..\nThis Log Sent to the Owner."),
                true);
            $interaction->guild->owner->sendMessage(MessageBuilder::new()->setContent("<@{$interaction->user->id}> Tried To Confirm Gang"));

            return false;
        }

        $discord_request = explode('+', $interaction->data->custom_id)[1];
        $gangRequest = GangCreationRequest::where('discord_id', '=', $discord_request)->first();
        $gangData = DB::connection('second_db')->table('gangs_data')->where('name', '=',
            strtolower($gangRequest->gang_name))->first();

        $gangMembers = explode(',', $gangRequest->members);
        $gangMembers[] = $gangRequest->boss;
        $gangMembers[] = $gangRequest->co_boss;

        foreach (Player::all() as $player) {
            foreach ($gangMembers as $member) {
                if ($player->metadata->discord === 'discord:' . $gangRequest->boss) {
                    Gang::updateOrCreate(['name' => $gangData->name], [
                        'name'    => $gangData->name,
                        'owner'   => $player->citizenid,
                        'zones'   => '[]',
                        'picture' => '',
                        'color'   => "#{$gangData->color_hex}",
                    ]);
                }
                if ($player->metadata->discord === 'discord:' . $member) {
                    Criminal::where('identifier', '=', $player->citizenid)->update(['organization' => $gangData->name]);
                }
            }
        }


        $guild = $this->discord->guilds->get('id', $_ENV['DISCORD_BOT_GUILD']);

        $request = GangCreationRequest::where('discord_id', '=',
            $interaction->user->id)->first();

        $ch = $guild->channels->get('id', $request->channel_id);

        $fields = [
            ['name' => 'Approved By', 'value' => "<@{$interaction->user->id}>"]
        ];
        $embed = $this->embed($this->client, $fields, 'Action Approved');
        $interaction->user->id = $request->discord_id;
        $builder = $this->messageSummaryRequest($interaction);
        $builder->addEmbed($embed);
        $ch->sendMessage(MessageBuilder::new()->addEmbed($embed))->done();
        $interaction->message->delete();
        $interaction->channel->sendMessage($builder)->done();
        $interaction->acknowledge();

        return true;
    }

}