<?php

namespace Mush\Disease\Service;

use Mush\Action\Entity\Action;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Player\Entity\Player;

interface SymptomActivationRequirementServiceInterface
{
    public function getActiveSymptoms(SymptomConfigCollection $symptomConfigs, Player $player, array $tags, Action $action = null): SymptomConfigCollection;
}
