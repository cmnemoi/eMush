<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Collection\PlanetSectorConfigCollection;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\SpaceCoordinates;
use Mush\Exploration\Repository\PlanetRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;

final class PlanetService implements PlanetServiceInterface
{
    private EntityManagerInterface $entityManager;
    private PlanetRepository $planetRepository;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PlanetRepository $planetRepository,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->planetRepository = $planetRepository;
        $this->randomService = $randomService;
    }

    public function createPlanet(Player $player): Planet
    {
        if ($player->getPlanets()->count() === $player->getPlayerInfo()->getCharacterConfig()->getMaxDiscoverablePlanets()) {
            throw new \Exception('Player already discovered the maximum number of planets');
        }

        $daedalus = $player->getDaedalus();

        $planet = new Planet($player);
        $planet
            ->setName($this->getPlanetName())
            ->setSize($this->getPlanetSize($daedalus))
        ;

        $planet->setCoordinates($this->getCoordinatesForPlanet($planet));

        $planet = $this->generatePlanetSectors($planet);

        $this->persist([$planet]);

        return $planet;
    }

    public function revealPlanetSectors(Planet $planet, int $number): Planet
    {
        $sectorsToReveal = $this->randomService->getRandomPlanetSectorsToReveal($planet, $number);

        $revealedSectors = $sectorsToReveal->map(fn (PlanetSector $sector) => $sector->reveal());

        $this->persist($revealedSectors->toArray());

        return $planet;
    }

    public function findById(int $id): ?Planet
    {
        return $this->planetRepository->find($id);
    }

    public function findOneByDaedalusDestination(Daedalus $daedalus): ?Planet
    {
        return $this->planetRepository->findOneByDaedalusDestination($daedalus);
    }

    public function findPlanetInDaedalusOrbit(Daedalus $daedalus): ?Planet
    {
        if (!$daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT)) {
            return null;
        }

        return $this->findAllByDaedalus($daedalus)->first();
    }

    public function findAllByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->planetRepository->findAllByDaedalus($daedalus));
    }

    public function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            if ($entity instanceof Planet) {
                $entity->getPlayer()->removePlanet($entity);
            }
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }

    /**
     * Function to get coordinates for a planet. The rules are as follows:
     * 1) Generate all planets within a distance between 2 and 7. To get the distance, roll 2 dices [2-7] and take the average of the two rolls
     * 2) If no planet is available, generate planets with a distance of 8
     * 3) If no planet is available, generate planets with a distance of 9
     */
    private function getCoordinatesForPlanet(Planet $planet): SpaceCoordinates
    {
        // Find available coordinates for a planet. First, we try to find coordinates with a distance between 2 and 7
        // Then planets of distance 8, then planets of distance 9
        $availableCoordinates = new ArrayCollection();
        $maxDistance = 7;
        for ($maxDistance; $maxDistance <= 9; ++$maxDistance) {
            // we don't want two planets to have the same coordinates, so we have to check if the coordinates are available
            // under the max distance given
            $availableCoordinates = $this->getAvailableCoordinatesForPlanetUnderDistance($planet, $maxDistance);
            if (!$availableCoordinates->isEmpty()) {
                break;
            }
        }

        // Determine the range for the double roll. If the max distance is 7, the range is 2-7.
        // Otherwise, the range is a unique value (8 or 9)
        $minDistance = $maxDistance <= 7 ? 2 : $maxDistance;

        // Draw the planet distance with a subtlety : if no coordinates for the drawn distance are available,
        // roll again until a valid distance is drawn
        $drawnCoordinates = null;
        while (!$drawnCoordinates) {
            $chosenDistance = $this->randomService->rollTwiceAndAverage($minDistance, $maxDistance);
            $coordinatesAtDistance = $availableCoordinates->filter(
                fn (SpaceCoordinates $coordinates) => $coordinates->getDistance() === $chosenDistance
            )->toArray();
            $drawnCoordinates = $this->randomService->getRandomElement($coordinatesAtDistance);
        }

        return $drawnCoordinates;
    }

    private function getAvailableCoordinatesForPlanetUnderDistance(Planet $planet, int $distance): ArrayCollection
    {
        $availableCoordinates = SpaceCoordinates::getAll()->filter(
            fn (SpaceCoordinates $coordinates) => $coordinates->getDistance() <= $distance
        );

        $existingPlanets = $this->planetRepository->findAllByDaedalus($planet->getDaedalus());
        foreach ($existingPlanets as $existingPlanet) {
            foreach ($availableCoordinates as $coordinates) {
                if ($existingPlanet->getCoordinates()->equals($coordinates)) {
                    $availableCoordinates->removeElement($coordinates);
                }
            }
        }

        return $availableCoordinates;
    }

    private function getPlanetName(): PlanetName
    {
        $planetName = new PlanetName();
        $planetName->setFirstSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_FIRST_SYLLABLES));
        $planetName->setFourthSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_FOURTH_SYLLABLES));

        if ($this->randomService->isSuccessful(10)) {
            $planetName->setSecondSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_SECOND_SYLLABLES));
        }

        if ($this->randomService->isSuccessful(40)) {
            $planetName->setThirdSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_THIRD_SYLLABLES));
        }

        if ($this->randomService->isSuccessful(3)) {
            $planetName->setFifthSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_FIFTH_SYLLABLES));
        } elseif ($this->randomService->isSuccessful(30)) {
            $planetName->setPrefix($this->randomService->random(1, PlanetName::NUMBER_OF_PREFIXES));
        }

        $this->persist([$planetName]);

        return $planetName;
    }

    private function getPlanetSize(Daedalus $dadalus): int
    {
        if ($dadalus->isInHardMode()) {
            return 4 + $this->randomService->random(0, 6) * 2;
        } elseif ($dadalus->isInVeryHardMode()) {
            return 6 + $this->randomService->random(0, 7) * 2;
        }

        return 2 + $this->randomService->random(0, 5) * 2;
    }

    private function generatePlanetSectors(Planet $planet): Planet
    {
        /** @var ArrayCollection<int, PlanetSector> $sectors */
        $sectors = new ArrayCollection();

        // We need to clone the sector configs collection because we will remove some from it
        // during the generation process and we don't want to persist this
        $storedSectorConfigs = $planet->getDaedalus()->getGameConfig()->getPlanetSectorConfigs();
        $inMemorySectorConfigs = clone $storedSectorConfigs;
        $total = $this->getSectorConfigsTotalWeight($inMemorySectorConfigs);

        // Generate a sector for each available slot on the planet
        for ($i = 0; $i < $planet->getSize(); ++$i) {
            $random = $this->randomService->random(0, $total - 1);
            $sum = 0;

            // Iterate over all possible sectors
            /** @var PlanetSectorConfig $sectorConfig */
            foreach ($inMemorySectorConfigs as $sectorConfig) {
                // Add the weight of this sector to the running sum
                $sum += $sectorConfig->getWeightAtPlanetGeneration();

                // If the running sum is greater than the random number, add the sector to the planet
                if ($sum > $random) {
                    $sectors->add(new PlanetSector($sectorConfig, $planet));

                    // Decrement the maximum number of times this sector can appear on a planet
                    $sectorConfig->setMaxPerPlanet($sectorConfig->getMaxPerPlanet() - 1);

                    // If the maximum number of times this sector can appear on a planet has been reached, remove it from the list of available sectors
                    if ($sectorConfig->getMaxPerPlanet() === 0) {
                        $inMemorySectorConfigs->removeElement($sectorConfig);

                        // Subtract the weight of this sector configuration from the cumulated weight for next random draw
                        $total -= $sectorConfig->getWeightAtPlanetGeneration();
                    }

                    // Break out of the loop since we've generated a sector for this slot on the planet
                    break;
                }
            }

            // if there is no planet sector config available anymore, stop generation
            if ($total === 0) {
                break;
            }
        }

        // PHP is very bad at cloning objects so the original sector configs have been modified.
        // We need to refresh them from database to get the original values back
        $storedSectorConfigs->map(fn (PlanetSectorConfig $sectorConfig) => $this->entityManager->refresh($sectorConfig));

        $planet->setSectors($sectors);

        return $planet;
    }

    private function getSectorConfigsTotalWeight(PlanetSectorConfigCollection $sectorConfigs): int
    {
        $total = 0;
        foreach ($sectorConfigs as $sectorConfig) {
            $total += $sectorConfig->getWeightAtPlanetGeneration();
        }

        return $total;
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}
