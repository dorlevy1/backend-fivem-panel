<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Message;
use App\Models\Player;
use App\Models\Warn;

#[AllowDynamicProperties] class WarnRepository extends Message
{


    protected Warn $warn;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;


    public function __construct(Warn $warn)
    {
        $this->warn = $warn;
        $this->notify = new DatabaseChange('warnsUpdate', 'my-event');

    }

    public function getWarns()
    {

        return $this->warn->all();
    }

    private function updateInGameNotify($name): void
    {
        $this->inGameNotify = new DatabaseChange($name, 'my-event');
    }


    public function add($data)
    {

        $warn = Warn::create([
            'discord'   => strval($data->player['metadata']['discord']),
            'license'   => strval($data->player['license']),
            'name'      => $data->player['name'],
            'reason'    => $data->res['reason'],
            'warned_by' => $data->res['admin']
        ]);

        $this->updateInGameNotify('inGame.' . Player::where('license', '=',
                strval($data->player['license']))->first()->id);

        $this->inGameNotify->setData([
            'type'    => 'WARN',
            'message' => $data->res['reason'],
            'timeout' => 10000,
        ]);
        $this->inGameNotify->send($this->inGameNotify);

        $this->sendSocket($this->getWarns());

        $user = auth()->user();

        $discord_id = str_replace('discord:', '', $data->player['metadata']['discord']);
        $time = date('Y-m-d h:i:s');
        $fields = [
            [
                'name'  => 'Warn Details',
                'value' => "**Action By:** <@{$user->discord_id}>\n**Player:** <@{$discord_id}>\n**Time:** ||{$time}||\n**Reason:** ||{$data->res['reason']}||"
            ],
        ];


        $components['components'] = $this->createButtonComponent([
            [
                'type'      => 2,
                'label'     => "Click To Give Ban.",
                'style'     => 1,
                'custom_id' => "add_ban+{$discord_id}+{$data->res['reason']}"
            ]
        ]);


        $this->createMessage([
            'adminDiscordId' => $user->discord_id,
            'title'          => 'Warn Added',
            'description'    => "<@{$user->discord_id}> Give Warn To <@{$discord_id}>!",
            'webhook'        => "warns",
            'fields'         => $fields,
            'components'     => $components,
        ]);


        return $warn;
    }

    public function update($data)
    {
        $ban = Warn::find($data->id);
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
        $ban = Warn::find($id);
        if ( !$ban) {
            return (object)[
                'success' => false,
                'message' => 'No Ban Were Found'
            ];
        }
        $ban->delete();
        $this->sendSocket($this->warn->all());

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
