<?php


namespace App\Services;

use App\Repositories\GangRepository;
use App\Repositories\PermissionsRepository;

class PermissionsService
{

    private PermissionsRepository $permissionsRepository;

    public function __construct(PermissionsRepository $permissionsRepository)
    {
        $this->permissionsRepository = $permissionsRepository;
    }

    public function get()
    {
        return $this->permissionsRepository->get();
    }

    public function addPlayer($data)
    {
        return $this->permissionsRepository->addPlayer($data);
    }
}