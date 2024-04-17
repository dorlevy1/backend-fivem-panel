<?php


namespace App\Services;

use App\Helpers\AccessToken;
use App\Repositories\PlayerRepository;
use PHPUnit\Runner\ErrorException;

class PlayerService
{

    private PlayerRepository $playerRepository;

    public function __construct(PlayerRepository $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function getPlayers()
    {
        return $this->playerRepository->getPlayers();
    }

    public function playerConnecting($data): true
    {
        $this->playerRepository->onlineNotify->setData(json_decode($data));
        $this->playerRepository->onlineNotify->send($this->playerRepository->onlineNotify);

        return true;
    }

    public function playerDisconnect($data): true
    {
        $this->playerRepository->onlineNotify->setData(json_decode($data));
        $this->playerRepository->onlineNotify->send($this->playerRepository->onlineNotify);

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