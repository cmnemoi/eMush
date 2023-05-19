<?php

namespace Mush\Modifier\Service;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

interface EventCreationServiceInterface
{
    public function createEvents(
        AbstractEventConfig $eventConfig,
        ModifierHolder $modifierHolder,
        ?Player $player,
        array $tags,
        \DateTime $time,
        bool $reverse = false
    ): array;
}
