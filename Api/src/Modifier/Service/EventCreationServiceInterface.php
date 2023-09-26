<?php

namespace Mush\Modifier\Service;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\ModifierHolderInterface;

interface EventCreationServiceInterface
{
    public function createEvents(
        AbstractEventConfig $eventConfig,
        ModifierHolderInterface $modifierRange,
        int $priority,
        array $tags,
        \DateTime $time,
        bool $reverse = false
    ): EventChain;
}
