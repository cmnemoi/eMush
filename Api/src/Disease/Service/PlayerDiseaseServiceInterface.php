<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Player\Entity\Player;

interface PlayerDiseaseServiceInterface
{
    public function persist(PlayerDisease $playerDisease): PlayerDisease;

    public function delete(PlayerDisease $playerDisease): void;

    public function removePlayerDisease(
        PlayerDisease $playerDisease,
        array $causes,
        \DateTime $time,
        string $visibility,
        Player $author = null): bool;

    public function createDiseaseFromName(
        string $diseaseName,
        Player $player,
        array $reasons,
        int $delayMin = null,
        int $delayLength = null
    ): PlayerDisease;

    public function handleNewCycle(PlayerDisease $playerDisease, \DateTime $time): void;

    public function healDisease(Player $author, PlayerDisease $playerDisease, array $reasons, \DateTime $time, string $visibility): void;
}
