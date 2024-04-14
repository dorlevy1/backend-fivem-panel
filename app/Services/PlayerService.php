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