<?php

namespace Mush\Modifier\Service;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\ModifierHolder;

interface EventCreationServiceInterface
{
    public function createEvents(
        AbstractEventConfig $eventConfig,
        ModifierHolder $modifierRange,
        array $tags,
        \DateTime $time,
        bool $reverse = false
    ): array;
}
