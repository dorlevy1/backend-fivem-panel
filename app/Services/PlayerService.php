<?php


namespace App\Services;

use App\Events\DatabaseChange;
use App\Helpers\AccessToken;
use App\Repositories\PlayerRepository;
use PHPUnit\Runner\ErrorException;

class PlayerService
{

    private PlayerRepository $playerRepository;
    private DatabaseChange $connectingNotify;
    private DatabaseChange $disconnectNotify;

    public function __construct(PlayerRepository $playerRepository)
    {
        $this->playerRepository = $playerRepository;
        $this->connectingNotify = new DatabaseChange('onlinePlayersUpdate', 'connecting');
        $this->disconnectNotify = new DatabaseChange('onlinePlayersUpdate', 'disconnect');

    }

    public function getPlayers()
    {
        return $this->playerRepository->getPlayers();
    }

    public function playerConnecting($data): true
    {
        
        $this->connectingNotify->setData($data);
        $this->connectingNotify->send($this->connectingNotify);

        return true;
    }

    public function playerDisconnect($data): true
    {
        $this->disconnectNotify->setData(json_decode($data));
        $this->disconnectNotify->send($this->disconnectNotify);

        return true;
    }

    public function getJoinedPlayers(): object
    {
        return $this->playerRepository->getJoinedPlayers();
    }

    public function giveBan($citizenid)
    {
        return $this->playerRepository->giveBan($citizenid);
    }

    public function getPlayer($data)
    {
        return $this->playerRepository->getPlayerByCitizenID($data);
    }

    public function getInventory($data)
    {
        return $this->playerRepository->getInventory($data);
    }
}