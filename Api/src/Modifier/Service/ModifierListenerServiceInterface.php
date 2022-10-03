<?php

namespace Mush\Modifier\Service;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;

interface ModifierListenerServiceInterface
{
    public function applyModifiers(AbstractModifierHolderEvent $event): bool;

    public function canHandle(AbstractGameEvent $event): bool;

    public function harvestAppliedModifier(AbstractModifierHolderEvent $event): array;
}
