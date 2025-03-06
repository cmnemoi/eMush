<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;

class RandomService implements RandomServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepository $gameEquipmentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepository $gameEquipmentRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->gameEquipmentRepository = $gameEquipmentRepository;
    }

    public function random(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public function poissonRandom(float $lambda): int
    {
        if ($lambda < 0) {
            throw new \Exception("poissonRandom: lambda ({$lambda}) must be positive");
        }

        $L = exp(-$lambda);
        $k = 0;
        $p = 1;

        do {
            ++$k;
            $p *= $this->randomPercent() / 100;
        } while ($p > $L);

        return $k - 1;
    }

    public function randomPercent(): int
    {
        return $this->random(1, 100);
    }

    public function rollTwiceAndAverage(int $min, int $max): int
    {
        return (int) (($this->random($min, $max) + $this->random($min, $max)) / 2);
    }

    public function isSuccessful(int $successRate): bool
    {
        return $this->randomPercent() <= $successRate;
    }

    public function isActionSuccessful(int $successRate): bool
    {
        return $this->rollTwiceAndAverage(1, 100) <= $successRate;
    }

    public function outputCriticalChances(int $successRate, int $criticalFailRate = 0, int $criticalSuccessRate = 0): string
    {
        $chance = $this->randomPercent();

        if ($criticalFailRate > $successRate || 100 - $criticalSuccessRate < $successRate) {
            throw new \Exception("criticalFailRate ({$criticalFailRate})
            must be lower than successRate ({$successRate}) and
            100-criticalSuccessRate ({100 - {$criticalSuccessRate}}) higher than successRate ({$successRate})");
        }

        if ($chance <= $criticalFailRate) {
            return ActionOutputEnum::CRITICAL_FAIL;
        }
        if ($chance <= $successRate) {
            return ActionOutputEnum::FAIL;
        }
        if ($chance <= 100 - $criticalSuccessRate) {
            return ActionOutputEnum::SUCCESS;
        }
        if ($chance <= 100) {
            return ActionOutputEnum::CRITICAL_SUCCESS;
        }

        throw new \Exception("input percentages ({$chance}) should range between 0 and 100");
    }

    public function getRandomPlayer(PlayerCollection $players): Player
    {
        if ($players->isEmpty()) {
            return PlayerFactory::createNullPlayer();
        }

        return current($this->getRandomElements($players->toArray()));
    }

    public function getRandomDisease(PlayerDiseaseCollection $collection): PlayerDisease
    {
        if ($collection->isEmpty()) {
            throw new \Exception('getRandomDisease: collection is empty');
        }

        return current($this->getRandomElements($collection->toArray()));
    }

    /**
     * This method returns a random `number` of hunters from a Daedalus `hunterPool`, according to hunters' draw weight.
     */
    public function getRandomHuntersInPool(HunterCollection $hunterPool, int $number): HunterCollection
    {
        if ($hunterPool->isEmpty()) {
            throw new \Exception('getRandomHuntersInPool: collection is empty');
        }

        $hunterProbaCollection = $hunterPool->getProbaCollection();
        $selectedHuntersIds = array_values($this->getRandomElementsFromProbaCollection($hunterProbaCollection, $number));

        return $hunterPool->map(static fn (Hunter $hunter) => \in_array($hunter->getId(), $selectedHuntersIds, true) ? $hunter : null)
            ->filter(static fn (?Hunter $hunter) => $hunter instanceof Hunter);
    }

    public function getPlayerInRoom(Place $place): Player
    {
        return $this->getRandomPlayer($place->getPlayers()->getPlayerAlive());
    }

    public function getAlivePlayerInDaedalus(Daedalus $ship): Player
    {
        return $this->getRandomPlayer($ship->getPlayers()->getPlayerAlive());
    }

    public function getItemInRoom(Place $place): GameItem
    {
        if ($place->getEquipments()->isEmpty()) {
            throw new \Exception("there isn't any item in this room");
        }

        $items = $place->getEquipments()->filter(static fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        return current($this->getRandomElements($items->toArray()));
    }

    public function getRandomElements(array $array, int $number = 1): array
    {
        if (empty($array)) {
            return [];
        }
        if ($number > \count($array)) {
            $number = \count($array);
        }

        $result = [];
        for ($i = 0; $i < $number; ++$i) {
            $keysNotPicked = array_values(array_diff(array_keys($array), array_keys($result)));

            $key = $keysNotPicked[$this->random(0, \count($keysNotPicked) - 1)];
            $result[$key] = $array[$key];
        }

        return $result;
    }

    public function getRandomElement(array $array): mixed
    {
        $draw = $this->getRandomElements($array, 1);
        $element = current($draw);

        if (!$element) {
            return null;
        }

        return $element;
    }

    /** This function takes an array [element => proba%] as input and send back an array
     * Instead of proba relative weight also work.
     */
    public function getSingleRandomElementFromProbaCollection(ProbaCollection $array): null|int|string
    {
        if (\count($array) < 1) {
            return null;
        }

        $cumuProba = $array->getTotalWeight();
        if ($cumuProba === 0) {
            return null;
        }

        $probaLim = $this->random(1, $cumuProba);

        return $array->getElementFromDrawnProba($probaLim);
    }

    // This function takes a ProbaCollection as input and send back an array
    public function getRandomElementsFromProbaCollection(ProbaCollection $array, int $number): array
    {
        $number = min($number, \count($array));

        $randomElements = [];
        for ($i = 0; $i < $number; ++$i) {
            $newElement = $this->getSingleRandomElementFromProbaCollection($array->withdrawElements($randomElements));

            if ($newElement !== null) {
                $randomElements[$i] = $newElement;
            }
        }

        return $randomElements;
    }

    public function getRandomDaedalusEquipmentFromProbaCollection(ProbaCollection $array, int $number, Daedalus $daedalus): array
    {
        $equipmentNames = $this->getRandomElementsFromProbaCollection($array, $number);

        $equipments = [];
        foreach ($equipmentNames as $equipmentName) {
            try {
                $equipment = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus)[0];
            } catch (\Exception $e) {
                continue;
            }
            $equipments[] = $equipment;
        }

        return $equipments;
    }

    public function getRandomPlanetSectorsToReveal(Planet $planet, int $number): ArrayCollection
    {
        $sectorIdsToReveal = $this->getRandomElementsFromProbaCollection(
            array: $this->getPlanetSectorsToRevealProbaCollection($planet),
            number: $number,
        );

        return $this->getPlanetSectorCollectionFromIds($sectorIdsToReveal);
    }

    public function getRandomPlanetSectorsToVisit(Planet $planet, int $number): ArrayCollection
    {
        $sectorIdsToVisit = $this->getRandomElementsFromProbaCollection(
            array: $this->getPlanetSectorsToVisitProbaCollection($planet),
            number: $number,
        );

        return $this->getPlanetSectorCollectionFromIds($sectorIdsToVisit);
    }

    public function getRandomXylophNameToDecode(array $xylophArray): null|int|string
    {
        $xylophProbaCollection = $this->getXylophNameProbaCollection($xylophArray);

        return $this->getSingleRandomElementFromProbaCollection($xylophProbaCollection);
    }

    public function getRandomUniqueMageBookName(Daedalus $daedalus): null|int|string
    {
        $mageBookProbaCollection = $this->getUniqueMageBookProbaCollection($daedalus);

        return $this->getSingleRandomElementFromProbaCollection($mageBookProbaCollection);
    }

    private function getPlanetSectorsToRevealProbaCollection(Planet $planet): ProbaCollection
    {
        $probaCollection = new ProbaCollection();
        foreach ($planet->getUnrevealedSectors() as $sector) {
            $probaCollection->setElementProbability($sector->getId(), $sector->getWeightAtPlanetAnalysis());
        }

        return $probaCollection;
    }

    private function getPlanetSectorsToVisitProbaCollection(Planet $planet): ProbaCollection
    {
        $probaCollection = new ProbaCollection();
        foreach ($planet->getUnvisitedSectors() as $sector) {
            $chanceToVisitSector = $sector->getWeightAtPlanetExploration();
            if ($sector->getName() === PlanetSectorEnum::HYDROCARBON && $planet->getExploration()?->hasAFunctionalEcholocator()) {
                $chanceToVisitSector *= 5;
            }
            if (PlanetSectorEnum::getLifeForms()->contains($sector->getName()) && $planet->getExploration()?->hasAFunctionalThermosensor()) {
                $chanceToVisitSector *= 5;
            }

            $probaCollection->setElementProbability($sector->getId(), $chanceToVisitSector);
        }

        return $probaCollection;
    }

    private function getPlanetSectorCollectionFromIds(array $sectorIds): ArrayCollection
    {
        /** @var ArrayCollection<int, PlanetSector> $sectors */
        $sectors = new ArrayCollection();
        foreach ($sectorIds as $sectorId) {
            $sector = $this->entityManager->find(PlanetSector::class, $sectorId);
            if (!$sector) {
                throw new \RuntimeException("Sector {$sectorId} not found");
            }
            $sectors->add($sector);
        }

        return $sectors;
    }

    private function getXylophNameProbaCollection(array $xylophArray): ProbaCollection
    {
        $probaCollection = new ProbaCollection();

        foreach ($xylophArray as $xylophEntry) {
            $probaCollection->setElementProbability($xylophEntry->getName()->toString(), $xylophEntry->getWeight());
        }

        return $probaCollection;
    }

    private function getUniqueMageBookProbaCollection(Daedalus $daedalus): ProbaCollection
    {
        $apprentronProbaCollection = $daedalus->getDaedalusConfig()->getStartingApprentrons();
        $apprentronNamesToExclude = $daedalus->getUniqueItems()->getUniqueItemNames();

        return $apprentronProbaCollection->withdrawElements($apprentronNamesToExclude);
    }
}
