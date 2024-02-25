<?php

namespace Mush\Modifier\Service;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;

interface EventModifierServiceInterface
{
    public function applyModifiers(AbstractGameEvent $initialEvent, array $priorities): EventChain;
}
