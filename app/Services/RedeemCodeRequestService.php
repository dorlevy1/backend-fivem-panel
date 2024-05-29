<?php


namespace App\Services;

use App\Repositories\BanRepository;
use App\Repositories\RedeemCodeRequestRepository;
use App\Repositories\WarnRepository;

class RedeemCodeRequestService
{

    private RedeemCodeRequestRepository $redeemCodeRequestRepository;

    public function __construct(RedeemCodeRequestRepository $redeemCodeRequestRepository)
    {
        $this->redeemCodeRequestRepository = $redeemCodeRequestRepository;
    }

    public function all()
    {
        return $this->redeemCodeRequestRepository->all();
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