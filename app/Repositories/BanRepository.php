<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Ban;
use Illuminate\Http\Request;

class BanRepository
{

    protected Ban $bans;
    protected DatabaseChange $notify;

    public function __construct(Ban $bans)
    {
        $this->bans = $bans;
        $this->notify = new DatabaseChange('bansUpdate', 'my-event');
    }

    public function getBans()
    {
        return $this->bans->all();
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