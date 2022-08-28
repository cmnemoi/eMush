<?php

namespace Mush\Disease\Service;

use Mush\Action\Entity\Action;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Player\Entity\Player;

interface SymptomConditionServiceInterface
{
    public function getActiveSymptoms(SymptomConfigCollection $symptomConfigs, Player $player, string $reason, ?Action $action = null): SymptomConfigCollection;
}
