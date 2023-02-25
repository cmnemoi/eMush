<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;

/**
 * One of the modifier type
 * This type of modifier prevent the targetEvent from being dispatched.
 */
#[ORM\Entity]
class PreventEventModifierConfig extends EventModifierConfig
{
}
