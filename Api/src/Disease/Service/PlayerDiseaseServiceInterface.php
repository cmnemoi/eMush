<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Player\Entity\Player;

interface PlayerDiseaseServiceInterface
{
    public function persist(PlayerDisease $playerDisease): PlayerDisease;

    public function delete(PlayerDisease $playerDisease): bool;

    public function createDiseaseFromName(string $diseaseName, Player $player): PlayerDisease;

    public function handleDiseaseForCause(string $cause, Player $player): void;
}
