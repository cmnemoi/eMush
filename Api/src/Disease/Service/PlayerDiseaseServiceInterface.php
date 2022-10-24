<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Player\Entity\Player;

interface PlayerDiseaseServiceInterface
{
    public function persist(PlayerDisease $playerDisease): PlayerDisease;

    public function removePlayerDisease(
        PlayerDisease $playerDisease,
        string $cause,
        \DateTime $time,
        string $visibility,
        Player $author = null): bool;

    public function createDiseaseFromName(
        string $diseaseName,
        Player $player,
        string $cause,
        int $delayMin = null,
        int $delayLength = null
    ): ?PlayerDisease;

    public function handleDiseaseForCause(string $cause, Player $player, int $delayMin = null, int $delayLength = null): void;

    public function handleNewCycle(PlayerDisease $playerDisease, \DateTime $time): void;

    public function healDisease(Player $author, PlayerDisease $playerDisease, string $reason, \DateTime $time): void;
}
