<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Helpers\Discord\DiscordMessage;
use App\Models\Ban;
use App\Models\Player;
use App\Notifications\WebhookNotification;
use Illuminate\Http\Request;

#[AllowDynamicProperties] class BanRepository
{

    protected Ban $bans;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;

    public function __construct(Ban $bans)
    {
        $this->bans = $bans;
        $this->notify = new DatabaseChange('bansUpdate', 'my-event');
        $this->discordMessage = new DiscordMessage();
    }

    public function getBans()
    {
        return $this->bans->all();
    }

    private function updateInGameNotify($name): void
    {
        $this->inGameNotify = new DatabaseChange($name, 'my-event');
    }

    public function add($data)
    {

        $ban = Ban::create([
            'discord'  => strval($data->player['metadata']['discord']),
            'license'  => strval($data->player['license']),
            'name'     => $data->player['name'],
            'ip'       => (new Request())->ip(),
            'reason'   => $data->res['reason'],
            'expire'   => $data->res['date'],
            'bannedby' => $data->res['admin']
        ]);

        $this->updateInGameNotify('inGame.' . Player::where('license', '=',
                strval($data->player['license']))->first()->id);

        $this->inGameNotify->setData([
            'type'     => 'BAN',
            'message'  => $data->res['reason'],
            'timeout'  => 10000,
            'banUntil' => strtotime($data->res['date'])

        ]);
        $this->inGameNotify->send($this->inGameNotify);
        $this->sendSocket($this->bans->all());
        $user = auth()->user();

        $discord_id = str_replace('discord:', '', $data->player['metadata']['discord']);
        $time = date('Y-m-d h:i:s');
        $fields = [
            [
                'name'  => 'Ban Details',
                'value' => "**Action By:** <@{$user->discord_id}>\n**Player:** <@{$discord_id}>\n**Ban Until:** ||{$time}||\n**Reason:** ||{$data->res['reason']}||"
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
                        "custom_id" => "cancel_ban+" . $discord_id
                    ]
                ]
            ],
            //            [
            //                "type"       => 1,
            //                "components" => [
            //                    [
            //                        "type"      => 6,
            //                        "label"     => "Click To Cancel Ban.",
            //                        "style"     => 1,
            //                        //                        "url"   => "https://google.com",
            //                        "custom_id" => "cancdel_ban"
            //                    ],
            //                ]
            //            ],
        ];

        $this->discordMessage->createMessage([
            'adminDiscordId' => $user->discord_id,
            'title'          => 'Ban Added',
            'description'    => "<@{$user->discord_id}> Give Ban To <@{$discord_id}>!",
            'webhook'        => "bans",
            'fields'         => $fields,
            'components'     => $components,

        ]);

        return $ban;
    }

    public function update($data)
    {
        $ban = Ban::find($data->id);
        if ( !$ban) {
            return (object)[
                'success' => false,
                'message' => 'No Ban Were Found'
            ];
        }
        $ban->expire = $data->expire;
        $ban->save();

        return (object)[
            'success' => true,
            'message' => 'Ban Expire Date Updated'
        ];
    }

    public function delete($id)
    {
        $ban = Ban::find($id);
        if ( !$ban) {
            return (object)[
                'success' => false,
                'message' => 'No Ban Were Found'
            ];
        }
        $ban->delete();


        return (object)[
            'success' => true,
            'message' => 'Ban Deleted Successfully'
        ];
    }

    private function sendSocket($data): void
    {
        $this->notify->setData($data);
        $this->notify->send($this->notify);
    }

}