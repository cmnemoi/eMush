<?php

namespace Mush\Modifier\Service;

use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;

interface EventCreationServiceInterface
{
    public function getEventTargetsFromModifierHolder(
        VariableEventConfig $eventConfig,
        ModifierActivationRequirementCollection $eventTargetRequirements,
        array $targetFilters,
        ModifierHolderInterface $range,
        ModifierProviderInterface $author
    ): array;
}
