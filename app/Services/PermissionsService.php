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


    public function update($data)
    {
        return $this->permissionsRepository->update($data);
    }

    public function delete($data)
    {
        return $this->permissionsRepository->delete($data);
    }

    public function pending_delete($id)
    {
        return $this->permissionsRepository->pending_delete($id);
    }
}
