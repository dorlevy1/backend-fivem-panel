<?php

namespace App;

use App\Helpers\Discord\Discord;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction as In;

interface Command
{



    public function addOptions();

}
