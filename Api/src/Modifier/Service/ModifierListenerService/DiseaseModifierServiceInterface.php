<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Player\Entity\Player;

interface DiseaseModifierServiceInterface
{
    public function newDisease(Player $player, PlayerDisease $playerDisease, array $tags, \DateTime $time): void;

    public function cureDisease(Player $player, PlayerDisease $playerDisease, array $tags, \DateTime $time): void;
}
