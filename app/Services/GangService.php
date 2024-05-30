<?php


namespace App\Services;

use App\Repositories\GangRepository;

class GangService
{

    private GangRepository $gangRepository;

    public function __construct(GangRepository $gangRepository)
    {
        $this->gangRepository = $gangRepository;
    }

    public function getGangs()
    {
        return $this->gangRepository->getGangs();
    }

    public function requests()
    {
        return $this->gangRepository->requests();
    }
}