<?php


namespace App\Repositories;

use App\Models\Ban;
use App\Models\Warn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarnRepository
{

    protected Warn $warn;

    public function __construct(Warn $warn)
    {
        $this->warn = $warn;
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

        return (object)[
            'success' => true,
            'message' => 'Ban Deleted Successfully'
        ];
    }

}