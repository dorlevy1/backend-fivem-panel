<?php

namespace App\Helpers\Discord;


use Discord\Builders\Components\ActionRow;

class ButtonFactory
{

    public static function create(ActionRow $actionRow): Button
    {
        return new Button($actionRow);
    }
}
