<?php

namespace App\Helpers\Discord;

use App\Models\Admin;
use App\Models\Ban;
use App\Enums\Interaction as InteractionEnum;
use App\Models\Player;
use App\Models\Webhook;
use App\Repositories\BanRepository;
use Carbon\Carbon;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Illuminate\Http\Request;
use Discord\Parts\Interactions\Interaction as In;

class Interaction extends DiscordMessage
{


    public function __construct($interaction)
    {
        parent::__construct();


        $name = explode('+', $interaction['data']['custom_id'])[0];

        return match ($name) {
            InteractionEnum::CANCEL_BAN->value => $this->cancel_ban($interaction),
            InteractionEnum::ADD_BAN->value => $this->add_ban($interaction),
            default => true,
        };

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

        $reply && $this->discordAPI->removeMessage(Webhook::where('name', '=', 'bans')->first()->channel_id,
            $reply);
        $this->message([
            'playerDiscordId' => $playerDiscordId,
            'adminDiscordId'  => $adminDiscordId,
            'fields'          => $fields
        ]);
        $ban->delete();

    }


    private function getPermissions($interaction): bool
    {
        foreach ($interaction['member']['roles'] as $role) {
            if (strtolower($role->name) === 'bans' || strtolower($role->name) === 'god') {
                $admin = Admin::where('discord_id', '=', $interaction['member']['user']->id)->first();
                if ( !is_null($admin)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function cancel_ban($interaction): bool
    {
        $discord_id_ban = explode('+', $interaction['data']['custom_id'])[1];
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
                $this->discordAPI->removeMessage(Webhook::where('name', '=', 'warns')->first()->channel_id,
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
                                            //                        "url"   => "https://google.com",
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
                            $this->discordAPI->removeMessage(Webhook::where('name', '=', 'warns')->first()->channel_id,
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

}