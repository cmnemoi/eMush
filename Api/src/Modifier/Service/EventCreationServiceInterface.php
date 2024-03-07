<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\ModifierHolderInterface;

interface EventCreationServiceInterface
{
    public function getEventTargetsFromModifierHolder(
        string $eventTarget,
        ModifierHolderInterface $holder,
    ): array;
}
