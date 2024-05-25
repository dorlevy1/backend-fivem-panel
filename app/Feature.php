<?php

namespace App;

use Discord\Parts\Guild\Guild;

interface Feature
{



    public function createCat();

    public function createMainChannel(Guild $guild);

    public function createButtonChannel(Guild $guild);

    public function createLogPage(Guild $guild);
}
