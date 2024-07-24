<?php

namespace App\Helpers\Discord;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button as ComponentButton;


class Button
{

    private ActionRow $action;

    public function __construct(ActionRow $action)
    {
        $this->action = $action;
    }


    public function get(): ActionRow
    {
        return $this->action;
    }

    public function button($label, int $style = null, $customId = null): static
    {
        if (is_null($style)) {
            $style = ComponentButton::STYLE_PRIMARY;
        }
        $b = ComponentButton::new($style)->setCustomId($customId)->setLabel($label);

        $this->action->addComponent($b);
        return $this;
    }
}
