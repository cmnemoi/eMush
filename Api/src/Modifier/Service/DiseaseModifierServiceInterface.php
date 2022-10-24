<?php

namespace Mush\Modifier\Service;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Player\Entity\Player;

interface DiseaseModifierServiceInterface
{
    public function newDisease(Player $player, DiseaseConfig $diseaseConfig): void;

    public function cureDisease(Player $player, DiseaseConfig $diseaseConfig): void;
}
