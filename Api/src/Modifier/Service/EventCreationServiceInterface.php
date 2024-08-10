<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;

interface EventCreationServiceInterface
{
    public function getEventTargetsFromModifierHolder(
        VariableEventConfig $eventConfig,
        Collection $eventTargetRequirements,
        array $targetFilters,
        ModifierHolderInterface $range,
        ModifierProviderInterface $author
    ): array;
}
