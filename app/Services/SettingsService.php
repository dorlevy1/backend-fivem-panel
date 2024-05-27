<?php


namespace App\Services;

use App\Repositories\BanRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\WarnRepository;

class SettingsService
{

    private SettingsRepository $settingsRepository;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    public function getWarns()
    {

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