<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Player\Entity\Player;

class PreparePercentageRollEvent extends PercentageRollEvent
{

    public const INJURY_ROLL = 'prepare_side_effect_roll_injury';
    public const CLUMSINESS_ROLL_RATE = 'prepare_side_effect_roll_clumsiness';
    public const DIRTY_ROLL_RATE = 'prepare_side_effect_roll_dirty';
    public const ACTION_ROLL_RATE = 'prepare_roll_action';

}