<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Ban;
use App\Models\Player;
use Illuminate\Http\Request;

class BanRepository
{

    protected Ban $bans;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;

    public function __construct(Ban $bans)
    {
        $this->bans = $bans;
        $this->notify = new DatabaseChange('bansUpdate', 'my-event');
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
            'type'    => 'BAN',
            'message' => $data->res['reason'],
            'timeout' => 10000,
            'banUntil'=> strtotime($data->res['date'])

        ]);
        $this->inGameNotify->send($this->inGameNotify);
        $this->sendSocket($this->bans->all());


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