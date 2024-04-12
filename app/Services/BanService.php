<?php


namespace App\Services;

use App\Repositories\BanRepository;

class BanService
{

    private BanRepository $banRepository;

    public function __construct(BanRepository $banRepository)
    {
        $this->banRepository = $banRepository;
    }

    public function getBans()
    {
        return $this->banRepository->getBans();
    }


    public function add($data)
    {
        return $this->banRepository->add($data);
    }

    public function update()
    {
        return $this->banRepository->update();
    }

    public function delete($id)
    {
        return $this->banRepository->delete($id);
    }
}