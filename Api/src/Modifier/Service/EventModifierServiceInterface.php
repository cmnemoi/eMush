<?php

namespace Mush\Modifier\Service;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;

interface EventModifierServiceInterface
{
    public function applyVariableModifiers(ModifierCollection $modifiers, AbstractGameEvent $event): AbstractGameEvent;
}
