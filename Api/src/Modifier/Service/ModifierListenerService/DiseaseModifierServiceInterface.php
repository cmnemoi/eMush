<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Player\Entity\Player;

interface DiseaseModifierServiceInterface
{
    public function newDisease(Player $player, DiseaseConfig $diseaseConfig, array $tags, \DateTime $time): void;

    public function cureDisease(Player $player, DiseaseConfig $diseaseConfig, array $tags, \DateTime $time): void;
}
