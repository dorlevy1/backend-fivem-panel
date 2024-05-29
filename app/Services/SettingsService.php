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

    public function all()
    {
        return $this->settingsRepository->all();
    }


    public function add($data)
    {
    }

    public function update($data)
    {
        return $this->settingsRepository->update($data);
    }

    public function delete($id)
    {
    }
}