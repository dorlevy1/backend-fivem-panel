<?php


namespace App\Services;

use App\Repositories\BanRepository;
use App\Repositories\DiscordBotFrontRepository;
use App\Repositories\WarnRepository;

class DiscordBotFrontService
{

    private DiscordBotFrontRepository $botFrontRepository;

    public function __construct(DiscordBotFrontRepository $botFrontRepository)
    {
        $this->botFrontRepository = $botFrontRepository;
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