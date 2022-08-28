<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Player\Entity\Player;

interface SymptomServiceInterface
{
    public function handleCycleSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handlePostActionSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleStatusAppliedSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;
}
