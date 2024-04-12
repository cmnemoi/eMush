<?php

namespace Mush\Disease\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

interface DiseaseCauseServiceInterface
{
    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void;

    public function handleConsumable(Player $player, GameEquipment $gameEquipment): void;

    public function findCauseConfigByDaedalus(string $causeName, Daedalus $daedalus): DiseaseCauseConfig;

    public function handleDiseaseForCause(string $cause, Player $player, ?int $delayMin = null, ?int $delayLength = null): PlayerDisease;
}
