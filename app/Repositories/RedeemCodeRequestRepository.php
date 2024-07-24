<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Message;
use App\Models\Player;
use App\Models\RedeemCode;
use App\Models\RedeemCodeRequest;
use App\Models\RedeemCodeRequestHistory;
use App\Models\Warn;

#[AllowDynamicProperties] class RedeemCodeRequestRepository extends Message
{


    protected RedeemCodeRequest $redeem;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;


    public function __construct(RedeemCodeRequest $redeem)
    {
        $this->redeem = $redeem;
        $this->notify = new DatabaseChange('warnsUpdate', 'my-event');

    }

    public function all()
    {
        return ['active' => RedeemCode::with('request')->get(), 'history' => RedeemCodeRequestHistory::all()];
    }

    private function updateInGameNotify($name): void
    {
    }


    public function add($data)
    {

    }

    public function update($data)
    {

    }

    public function delete($id)
    {

    }

    private function sendSocket($data): void
    {

    }

}
