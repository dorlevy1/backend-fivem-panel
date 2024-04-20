<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Warn;

class WarnRepository
{

    protected Warn $warn;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;

    public function __construct(Warn $warn)
    {
        $this->warn = $warn;
        $this->notify = new DatabaseChange('warnsUpdate', 'my-event');
        $this->inGameNotify = new DatabaseChange('inGame', 'my-event');
    }

    public function getWarns()
    {
        return $this->warn->all();
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

        $this->inGameNotify->setData([
            'type' => 'warn',
            'data' => $warn
        ]);
        $this->inGameNotify->send($this->inGameNotify);

        $this->sendSocket($warn);


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