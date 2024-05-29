<?php


namespace App\Services;

use App\Repositories\BanRepository;
use App\Repositories\GangRequestRepository;
use App\Repositories\WarnRepository;

class GangRequestService
{

    private GangRequestRepository $gangRequestRepository;

    public function __construct(GangRequestRepository $gangRequestRepository)
    {
        $this->gangRequestRepository = $gangRequestRepository;
    }

    public function all()
    {
        return $this->gangRequestRepository->all();

    }


    public function add($data)
    {
    }

    public function update()
    {
    }

    public function delete($id)
    {
    }
}