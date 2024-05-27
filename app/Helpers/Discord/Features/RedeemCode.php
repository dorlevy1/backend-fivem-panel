<?php

namespace App\Helpers\Discord\Features;

use App\Enums\Interaction as InteractionEnum;
use App\Feature;
use App\Helpers\API;
use App\Helpers\Discord\DiscordMessage;
use App\Models\Player;
use App\Models\RedeemCodeRequest;
use App\Models\RedeemCodeRequestHistory;
use App\Models\Webhook;
use Carbon\Carbon;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\Option;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\Components\TextInput;
use Discord\Builders\Components\UserSelect;
use Discord\Builders\MessageBuilder;
use Discord\Discord as DiscordPHP;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Interaction as In;
use Discord\Parts\User\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class RedeemCode extends DiscordMessage implements Feature
{


    public API $api;
    public DiscordMessage $message;
    public DiscordPHP $discord;
    public DiscordPHP $client;

    public function __construct(DiscordPHP $discord, DiscordPHP $client)
    {
        $this->api = new API();
        $this->discord = $discord;
        $this->client = $client;
        $this->message = new DiscordMessage($client);
        $this->handle();
    }


    public function createButtonChannel(Guild $guild): bool|string
    {

        try {

            $embed = $this->message->embed($this->client, [], '');
            $builder = MessageBuilder::new();
            $ar = ActionRow::new();
            $submit = Button::new(Button::STYLE_PRIMARY,
                'create_redeem_code')->setLabel('Create Redeem Code');
            $ar->addComponent($submit);
            $builder->addEmbed($embed);
            $builder->addComponent($ar);
            $guild->channels->get('name', 'create-redeem-code')->sendMessage($builder);

            return true;
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }

    }

    public function createMainChannel(Guild $guild): void
    {

        if ( !is_null($guild->channels->get('name', 'create-redeem-code'))) {
            return;
        }

        $category = Webhook::where('name', '=', 'Redeem Code Area')->first()->channel_id;


        $channel = $guild->channels->create([
            'name'      => 'create-redeem-code',
            'type'      => Channel::TYPE_GUILD_TEXT,
            'parent_id' => $category,
            'nsfw'      => false
        ]);


        $guild->channels->save($channel)->then(function (Channel $channel) use ($guild) {

            Webhook::updateOrCreate([
                'name' => $channel->name
            ], [
                'name'       => $channel->name,
                'channel_id' => $channel->id,
                'parent'     => false
            ]);

            $this->createButtonChannel($guild);

            return $channel;
        })->done();

    }

    public function handle(): void
    {

        $this->createCat();
        $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
        $this->createMainChannel($guild);
        $this->createLogPage($guild);

    }

    public function interaction(In $interaction)
    {
        $name = explode('+', $interaction->data->custom_id)[0];


        return match ($name) {
            InteractionEnum::REDEEM_CODE->value => $this->create($interaction),
            InteractionEnum::REDEEM_INSERT_CASH->value => $this->insert($interaction, 'Cash', ''),
            InteractionEnum::REDEEM_INSERT_ITEMS->value => $this->insert($interaction, 'Items', ''),
            InteractionEnum::REDEEM_INSERT_WEAPONS->value => $this->insert($interaction, 'Weapons', ''),
            InteractionEnum::REDEEM_INSERT_VEHICLES->value => $this->insert($interaction, 'Vehicles', ''),
            InteractionEnum::UPDATE_REDEEM->value => $this->update($interaction),
            InteractionEnum::DELETE_REDEEM->value => $this->delete($interaction),
            default => true,
        };
    }

    public function createCat(): Guild|string
    {
        try {
            $guild = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD'));
            if ( !is_null($guild->channels->get('name', 'Redeem Code Area'))) {
                return false;
            }
            $group = $guild->channels->create([
                'name' => 'Redeem Code Area',
                'type' => Channel::TYPE_GUILD_CATEGORY,
            ]);

            $guild->channels->save($group)->then(function (Channel $channel) use ($guild) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name'       => $channel->name,
                    'channel_id' => $channel->id,
                    'parent'     => true
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

            if ( !is_null($guild->channels->get('name', 'redeem-codes'))) {
                return;
            }

            $category = Webhook::where('name', '=', 'DLPanel')->first()->channel_id;


            $channel = $guild->channels->create([
                'name'      => 'redeem-codes',
                'type'      => Channel::TYPE_GUILD_TEXT,
                'parent_id' => $category,
                'nsfw'      => false
            ]);


            $guild->channels->save($channel)->then(function (Channel $channel) use ($guild) {
                Webhook::updateOrCreate([
                    'name' => $channel->name
                ], [
                    'name'       => $channel->name,
                    'channel_id' => $channel->id,
                    'parent'     => false
                ]);
            })->done();

            return;
        } catch (\ErrorException $e) {
            return;
        }
    }


    private function getRequest($cid, $discord)
    {
        return RedeemCodeRequest::where('discord_id', '=', $discord)->where("citizenid", '=', $cid)->first();
    }

    private function createSummaryRequest(RedeemCodeRequest $request)
    {
        $redeem_code = $request->redeem_code()->first();
        $fields = [];
        if ( !is_null($redeem_code)) {
            $fields[] = ['name' => 'Redeem Code:', 'value' => $redeem_code->code];
            $fields[] = ['name' => 'Created By:', 'value' => "<@{$request->request_by}>"];
            $fields[] = ['name' => 'Created Date:', 'value' => $redeem_code->created_at];
        }
        $fields[] = [
            'name'  => 'Player Data:',
            'value' => "**Discord** : <@{$request->discord_id}>\n**CitizenID** : {$request->citizenid}"
        ];

        if ( !empty($request->vehicles)) {
            $fields[] = ['name' => 'Vehicles', 'value' => str_replace(',', "\n", $request->vehicles)];
        }

        if ( !empty($request->weapons)) {
            $fields[] = ['name' => 'Weapons', 'value' => str_replace(',', "\n", $request->weapons)];
        }

        if ( !empty($request->items)) {
            $fields[] = ['name' => 'Items', 'value' => str_replace(',', "\n", $request->items)];
        }

        if ( !empty($request->cash)) {
            $fields[] = ['name' => 'Cash', 'value' => $request->cash];
        }


        return $this->embed($this->discord, $fields, 'Redeem Code Details');
    }

    private function create(In $interaction)
    {

        $builder = MessageBuilder::new();
        $select = UserSelect::new()->setPlaceholder('Select Member')->setMinValues(1)->setMaxValues(1)->setCustomId('select_user');
        $builder->addComponent($select);

        $select->setListener(function (In $in) {
            $member = $in->guild->members->get('id', $in->data->values[0]);
            $request = $this->getRequest(Player::cid($in->data->values[0]), $in->data->values[0]);
            if (is_null($request)) {
                RedeemCodeRequest::create([
                    'discord_id' => $in->data->values[0],
                    'citizenid'  => Player::cid($in->data->values[0]),
                    'request_by' => $in->user->id,
                    'created_at' => Carbon::now()
                ]);

                $this->chooseRedeemCode($in, $member);
                $in->acknowledge();

                return true;
            }
            $embed = $this->createSummaryRequest($request);
            $ar = $this->createButtons([
                [
                    'style'     => Button::STYLE_PRIMARY,
                    'custom_id' => 'update_redeem+' . $member->user->id,
                    'label'     => 'Update Redeem'
                ],
                [
                    'style'     => Button::STYLE_DANGER,
                    'custom_id' => 'delete_redeem+' . $member->user->id,
                    'label'     => 'Delete Redeem'
                ]
            ]);

            $in->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($ar), true);
            $in->acknowledge();

            return false;
        }, $this->discord);

        $interaction->respondWithMessage($builder, true);
        $interaction->acknowledge();

        return true;
    }

    private function chooseRedeemCode(In $in, Member $member): void
    {
        $builder = MessageBuilder::new();
        $select = StringSelect::new()->setPlaceholder('Select One Of The Choices');
        $select->addOption(Option::new('Items', 'items'));
        $select->addOption(Option::new('Weapons', 'weapons'));
        $select->addOption(Option::new('Vehicles', 'vehicles'));
        $select->addOption(Option::new('Cash', 'cash'));
        $select->setMinValues(1)->setMaxValues(1);
        $builder->addComponent($select);
        $in->sendFollowUpMessage($builder, true);

        $select->setListener(function (In $i) use ($member) {
            if ($i->data->values[0] !== 'vehicles' && $i->data->values[0] !== 'cash') {
                $contents = File::get(base_path("{$i->data->values[0]}.json"));
                $json = json_decode($contents);

                $items = "";
                $count = 0;
                $builder = MessageBuilder::new();
                foreach ($json as $key => $item) {
                    if ($i->data->values[0] === 'vehicles') {
                        $item->label = $item->name;
                    }
                    if (strlen($items) >= 950) {
                        $fields = [
                            ['name' => '## ' . ucfirst($i->data->values[0]) . ' Data ##', 'value' => $items]
                        ];
                        $embed = $this->embed($this->client, $fields, 'Items Data');
                        $builder->addEmbed($embed);
                        $count += 1;
                        $items = "";

                        if ($count === 5) {
                            $i->sendFollowUpMessage($builder, true);
                            $builder = MessageBuilder::new();
                            $count = 0;
                        }
                    }
                    $items .= "**" . $item->label . "**  - " . $key . "\n";
                }
            }
            $this->sendInsertRedeemType($i, $member);
            $i->acknowledge();
        }, $this->discord);

        $in->acknowledge();
    }

    private function sendInsertRedeemType(In $i, Member $member): void
    {
        $builder = MessageBuilder::new();
        $ar = ActionRow::new();
        $button = Button::new(Button::STYLE_PRIMARY)->setCustomId('redeem_insert_' . $i->data->values[0] . '+' . $member->user->id);
        $button->setLabel('Add ' . ucfirst($i->data->values[0]));
        $ar->addComponent($button);
        $builder->addComponent($ar);
        $i->sendFollowUpMessage($builder, true);
        $i->acknowledge();
    }

    private function insert(In $interaction, $type, $label)
    {

        $discord_id = explode('+', $interaction->data->custom_id)[1];

        $interaction->showModal('Add ' . $type . ' Codes', Carbon::now(),
            [
                ActionRow::new()->addComponent(TextInput::new($label,
                    TextInput::STYLE_PARAGRAPH)->setMaxLength(4000)->setCustomId(strtolower($type)))
            ],
            function (In $in, Collection $components) use ($discord_id, $type) {
                $member = $in->guild->members->get('id', $discord_id);
                $request = RedeemCodeRequest::where(['discord_id' => $discord_id])->first();
                $type = strtolower($type);
                $value = $type !== 'cash' ?
                    implode(',', array_merge(explode(',', $request->$type), explode(',', $components[$type]['value'])))
                    : intval($request->$type) + intval($components[$type]['value']);


                $fields = [
                    ['name' => '', 'value' => str_replace(',', "\n", $value)],
                ];

                $request->$type = $value;
                $request->updated_at = Carbon::now();

                $request->save();

                $embed = $this->embed($this->discord, $fields, 'Chosen ' . $type);
                $builder = MessageBuilder::new()->addEmbed($embed);
                $ar = ActionRow::new();
                $button1 = Button::new(Button::STYLE_SUCCESS)->setCustomId('done_' . $type . '+' . $member->user->id);
                $button1->setLabel('Finish The Redeem');
                $button2 = Button::new(Button::STYLE_SECONDARY)->setCustomId('add_more_' . $type . '+' . $member->user->id);
                $button2->setLabel('Add More');
                $ar->addComponent($button1);
                $ar->addComponent($button2);
                $builder->addComponent($ar);
                $in->respondWithMessage($builder, true)->done();
                $in->acknowledge();

                $button1->setListener(function (In $interaction) use ($in, $member) {
                    $this->finishRedeem($in, $member);
                    $interaction->acknowledge();
                }, $this->discord);

                $button2->setListener(function (In $interaction) use ($in, $member) {
                    $this->chooseRedeemCode($in, $member);
                    $interaction->acknowledge();
                }, $this->discord);
                $in->acknowledge();

            });
        $interaction->acknowledge();

        return true;
    }

    private function generateCode($length = 35)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.-/\+';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0 ; $i < $length ; $i++) {

            if (strlen($randomString) % 6 == 0) {
                $randomString .= '.';
            }
            if (strlen($randomString) % 10 == 0) {
                $randomString .= '-/';
            }
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        $code = \App\Models\RedeemCode::where('code', '=', $randomString)->first();
        if ( !is_null($code)) {
            $this->generateCode();
        }

        return $randomString;
    }

    private function finishRedeem(In $in, Member $member)
    {

        $cid = Player::cid($member->user->id);

        $request = RedeemCodeRequest::where(['discord_id' => $member->user->id, 'citizenid' => $cid])->first();

        $redeem = \App\Models\RedeemCode::where('redeem_request', '=', $request->id)->first();
        if (is_null($redeem)) {
            $code = $this->generateCode();
            \App\Models\RedeemCode::create([
                'redeem_request' => $request->id,
                'code'           => $code,
                'created_at'     => Carbon::now()
            ]);
        }

        $embed = $this->createSummaryRequest($request);
        $in->sendFollowUpMessage(MessageBuilder::new()->addEmbed($embed), true)->done();
        $in->deleteOriginalResponse();
        $in->acknowledge();

        $webhook = Webhook::where('name', '=', 'redeem-codes')->first()->channel_id;

        $channel = $this->discord->guilds->get('id', env('DISCORD_BOT_GUILD_LOGS'))->channels->get('id', $webhook);
        $channel->sendMessage(MessageBuilder::new()->addEmbed($embed));
    }

    private function update(In $interaction)
    {
        $discord = explode('+', $interaction->data->custom_id)[1];
        $member = $interaction->guild->members->get('id', $discord);

        $this->chooseRedeemCode($interaction, $member);

    }

    private function delete(In $interaction)
    {
        $discord = explode('+', $interaction->data->custom_id)[1];
        $request = RedeemCodeRequest::where('discord_id', '=', $discord)->first();
        $redeem = \App\Models\RedeemCode::where('redeem_request', '=', $request->id)->first();

        !is_null($redeem) && $redeem->delete();
        RedeemCodeRequestHistory::create([
            'request_by' => $request->request_by,
            'discord_id' => $request->discord_id,
            'citizenid'  => $request->citizenid,
            'weapons'    => $request->weapons,
            'vehicles'   => $request->vehicles,
            'items'      => $request->items,
            'cash'       => $request->cash,

        ]);
        $request->delete();

        $embed = $this->embed($this->discord, [], 'Redeem Deleted');
        $interaction->message->delete();
        $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed), true);


        $interaction->acknowledge();

        return true;
    }
}