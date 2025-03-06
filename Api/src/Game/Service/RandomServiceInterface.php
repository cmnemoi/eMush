<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameItem;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

interface RandomServiceInterface
{
    public function random(int $min, int $max): int;

    /** Generate a random number from a Poisson process (Knuth algorithm).
     *
     * P(k) = exp(-lambda) * lambda^k / k!
     */
    public function poissonRandom(float $lambda): int;

    public function randomPercent(): int;

    public function rollTwiceAndAverage(int $min, int $max): int;

    public function isSuccessful(int $successRate): bool;

    public function isActionSuccessful(int $successRate): bool;

    public function outputCriticalChances(int $successRate, int $criticalFailRate = 0, int $criticalSuccessRate = 0): string;

    public function getRandomPlayer(PlayerCollection $players): Player;

    public function getRandomDisease(PlayerDiseaseCollection $collection): PlayerDisease;

    public function getRandomHuntersInPool(HunterCollection $hunterPool, int $number): HunterCollection;

    public function getPlayerInRoom(Place $place): Player;

    public function getAlivePlayerInDaedalus(Daedalus $ship): Player;

    public function getItemInRoom(Place $place): GameItem;

    public function getRandomElements(array $array, int $number = 1): array;

    public function getRandomElement(array $array): mixed;

    public function getSingleRandomElementFromProbaCollection(ProbaCollection $array): null|int|string;

    public function getRandomElementsFromProbaCollection(ProbaCollection $array, int $number): array;

    public function getRandomDaedalusEquipmentFromProbaCollection(ProbaCollection $array, int $number, Daedalus $daedalus): array;

    public function getRandomPlanetSectorsToReveal(Planet $planet, int $number): ArrayCollection;

    public function getRandomPlanetSectorsToVisit(Planet $planet, int $number): ArrayCollection;

    public function getRandomXylophNameToDecode(array $xylophArray): null|int|string;

    public function getRandomUniqueMageBookName(Daedalus $daedalus): null|int|string;
}
