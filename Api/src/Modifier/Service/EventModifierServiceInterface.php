<?php

namespace Mush\Modifier\Service;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;

interface EventModifierServiceInterface
{
    public function applyModifiers(ModifierCollection $modifiers, AbstractGameEvent $initialEvent): EventChain;
}
