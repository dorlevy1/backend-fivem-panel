<?php


namespace App\Services;

use App\Repositories\BanRepository;
use App\Repositories\WarnRepository;

class WarnService
{

    private WarnRepository $warnRepository;

    public function __construct(WarnRepository $warnRepository)
    {
        $this->warnRepository = $warnRepository;
    }

    public function getWarns()
    {
        return $this->warnRepository->getWarns();
    }


    public function add($data)
    {
        return $this->warnRepository->add($data);
    }

    public function update()
    {
        return $this->warnRepository->update();
    }

    public function delete($id)
    {
        return $this->warnRepository->delete($id);
    }
}