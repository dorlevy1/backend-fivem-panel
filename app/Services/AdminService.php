<?php


namespace App\Services;

use App\Helpers\AccessToken;
use App\Repositories\AdminRepository;
use App\Repositories\PlayerRepository;
use PHPUnit\Runner\ErrorException;

class AdminService
{

    private AdminRepository $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function checkForPermissions($data)
    {
        return $this->adminRepository->checkForPermissions($data);
    }
}
