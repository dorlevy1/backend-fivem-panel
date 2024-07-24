<?php


namespace App\Repositories;

use AllowDynamicProperties;
use App\Events\DatabaseChange;
use App\Message;
use App\Models\GangCreationRequest;
use App\Models\Player;
use App\Models\Warn;

#[AllowDynamicProperties] class GangRequestRepository extends Message
{


    protected GangCreationRequest $gangCreationRequest;
    protected DatabaseChange $notify;
    protected DatabaseChange $inGameNotify;


    public function __construct(GangCreationRequest $gangCreationRequest)
    {
        $this->gangCreationRequest = $gangCreationRequest;
        $this->notify = new DatabaseChange('warnsUpdate', 'my-event');

    }

    public function all()
    {
        return $this->gangCreationRequest->all();
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
