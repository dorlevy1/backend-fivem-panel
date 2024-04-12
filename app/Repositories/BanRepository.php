<?php


namespace App\Repositories;

use App\Models\Ban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BanRepository
{

    protected Ban $bans;

    public function __construct(Ban $bans)
    {
        $this->bans = $bans;
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
            'reason'   => $data->res['reson'],
            'expire'   => $data->res['date'],
            'bannedby' => $data->res['bannedBy']
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

}