<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetConfig;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\SpaceCoordinates;
use Mush\Exploration\Enum\PlanetConfigsEnum;
use Mush\Exploration\Event\PlanetCreatedEvent;
use Mush\Exploration\Repository\PlanetRepository;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PlanetService implements PlanetServiceInterface
{
    public const int MAX_PLANET_DISTANCE = 7;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventServiceInterface $eventService,
        private PlanetRepository $planetRepository,
        private RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
    ) {}

    public function createPlanet(Player $player, string $planetConfigName = PlanetConfigsEnum::REGULAR, ?string $forcedSector = null, ?int $fixedSize = null): Planet
    {
        if ($player->getPlanets()->count() === $player->getPlayerInfo()->getCharacterConfig()->getMaxDiscoverablePlanets()) {
            throw new \Exception('Player already discovered the maximum number of planets');
        }

        $daedalus = $player->getDaedalus();

        $size = $fixedSize ?: $this->getPlanetSize($daedalus);

        $planet = new Planet($player);
        $planet
            ->setName($this->getPlanetName())
            ->setSize($size);

        $planet->setCoordinates($this->getCoordinatesForPlanet($planet));

        // we grab here the config that will be used to generate the planet
        $planetConfig = $player->getDaedalus()->getGameConfig()->getPlanetConfigs()
            ->filter(static fn (PlanetConfig $config) => $config->getName() === $planetConfigName)
            ->first();
        if (!$planetConfig instanceof PlanetConfig) {
            throw new \Exception("Missing planet config {$planetConfigName}.");
        }

        $planet = $this->generatePlanetSectors($planet, $planetConfig, $forcedSector);

        $this->persist([$planet]);

        $this->eventService->callEvent(new PlanetCreatedEvent($planet), PlanetCreatedEvent::class);

        return $planet;
    }

    public function regenerateAPlanet(Planet $planet, string $planetConfigName = PlanetConfigsEnum::REGULAR, ?string $forcedSector = null): Planet
    {
        // we delete all the current sectors
        $this->delete($planet->getSectors()->toArray());
        $planet->setSectors(new ArrayCollection([]));

        // we grab here the config that will be used to generate the planet
        $planetConfig = $planet->getPlayer()->getDaedalus()->getGameConfig()->getPlanetConfigs()
            ->filter(static fn (PlanetConfig $config) => $config->getName() === $planetConfigName)
            ->first();
        if (!$planetConfig instanceof PlanetConfig) {
            throw new \Exception("Missing planet config {$planetConfigName}.");
        }
        $planet = $this->generatePlanetSectors($planet, $planetConfig, $forcedSector);

        $this->persist([$planet]);

        $this->eventService->callEvent(new PlanetCreatedEvent($planet), PlanetCreatedEvent::class);

        return $planet;
    }

    public function revealPlanetSectors(Planet $planet, int $number): Planet
    {
        $sectorsToReveal = $this->randomService->getRandomPlanetSectorsToReveal($planet, $number);

        $revealedSectors = $sectorsToReveal->map(static fn (PlanetSector $sector) => $sector->reveal());

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
                $this->incrementRemovedCompletelyRevealedPlanets($entity);
                $entity->getPlayer()->removePlanet($entity);
            }
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    /**
     * Function to get coordinates for a planet. The rules are as follows:
     * 1) Generate all planets within a distance between 2 and 7. To get the distance, roll 2 dices [2-7] and take the average of the two rolls
     * 2) If no planet is available, generate planets with a distance of 8
     * 3) If no planet is available, generate planets with a distance of 9
     *
     * @psalm-suppress ReservedWord
     */
    private function getCoordinatesForPlanet(Planet $planet): SpaceCoordinates
    {
        // Find available coordinates for a planet. First, we try to find coordinates with a distance between 2 and 7
        // Then planets of distance 8, then planets of distance 9
        $availableCoordinates = new ArrayCollection();
        $maxDistance = self::MAX_PLANET_DISTANCE;
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
        $minDistance = $maxDistance <= self::MAX_PLANET_DISTANCE ? 2 : $maxDistance;

        // Draw the planet distance with a subtlety : if no coordinates for the drawn distance are available,
        // roll again until a valid distance is drawn
        $drawnCoordinates = null;
        while (!$drawnCoordinates) {
            $chosenDistance = $this->randomService->rollTwiceAndAverage($minDistance, $maxDistance);
            $coordinatesAtDistance = $availableCoordinates->filter(
                static fn (SpaceCoordinates $coordinates) => $coordinates->getDistance() === $chosenDistance
            )->toArray();
            $drawnCoordinates = $this->randomService->getRandomElement($coordinatesAtDistance);
        }

        return $drawnCoordinates;
    }

    /**
     * @psalm-suppress ReservedWord
     */
    private function getAvailableCoordinatesForPlanetUnderDistance(Planet $planet, int $distance): ArrayCollection
    {
        $availableCoordinates = SpaceCoordinates::getAll()->filter(
            static fn (SpaceCoordinates $coordinates) => $coordinates->getDistance() <= $distance
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

        if ($this->randomService->random(0, 10) === 0) {
            $planetName->setSecondSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_SECOND_SYLLABLES));
        }

        if ($this->randomService->random(0, 40) === 0) {
            $planetName->setThirdSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_THIRD_SYLLABLES));
        }

        if ($this->randomService->random(0, 3) === 0) {
            $planetName->setFifthSyllable($this->randomService->random(1, PlanetName::NUMBER_OF_FIFTH_SYLLABLES));
        } elseif ($this->randomService->random(0, 30) === 0) {
            $planetName->setPrefix($this->randomService->random(1, PlanetName::NUMBER_OF_PREFIXES));
        }

        $this->persist([$planetName]);

        return $planetName;
    }

    private function getPlanetSize(Daedalus $daedalus): int
    {
        if ($daedalus->isInHardMode()) {
            return 4 + $this->randomService->random(0, 6) * 2;
        }
        if ($daedalus->isInVeryHardMode()) {
            return 6 + $this->randomService->random(0, 7) * 2;
        }

        return 2 + $this->randomService->random(0, 5) * 2;
    }

    private function generatePlanetSectors(Planet $planet, PlanetConfig $config, ?string $forcedSector = null): Planet
    {
        // the array of sector returned
        /** @var ArrayCollection<int, PlanetSector> $sectors */
        $sectors = new ArrayCollection();

        // the configs of all the sector of that game config
        $sectorsConfig = $planet->getPlayer()->getDaedalus()->getGameConfig()->getPlanetSectorConfigs();

        $sectorsMaximum = $config->getMaximumSectors();
        $sectorWeight = $config->getSectorsWeight();

        // to avoid having the forced sector being the first one on the list, we choose a random position for it
        $sectorToReplaceByTheForcedSector = $this->randomService->random(0, $planet->getSize() - 1);

        // Generate a sector for each available slot on the planet
        for ($i = 0; $i < $planet->getSize(); ++$i) {
            // if forcedSector is not null and if we are at the selected positon, then we select that sector to be added instead of taking one randomly from the config
            if ($i === $sectorToReplaceByTheForcedSector && $forcedSector) {
                $sectors->add(new PlanetSector($sectorsConfig->getBySectorName($forcedSector), $planet));
            } else {
                $sectorSelected = $this->randomService->getSingleRandomElementFromProbaCollection($sectorWeight);
                if (!\is_string($sectorSelected)) {
                    throw new \Exception('No sector to be selected are left.');
                }

                // add the selected sector the result
                $sectors->add(new PlanetSector($sectorsConfig->getBySectorName($sectorSelected), $planet));

                // get the maximum amount
                $maximum = $sectorsMaximum->getElementProbability($sectorSelected);

                // Decrement the maximum number of times this sector can appear on a planet
                --$maximum;
                // save the result on list
                $sectorsMaximum->setElementProbability($sectorSelected, $maximum);

                // If the maximum number of times this sector can appear on a planet has been reached, remove it from the list of available sectors
                if ($maximum === 0) {
                    $sectorsMaximum->remove($sectorSelected);
                    $sectorWeight->remove($sectorSelected);
                }
            }

            // if there is no planet sector config available anymore, stop generation
            if ($sectorsMaximum->isEmpty()) {
                break;
            }
        }

        $planet->setSectors($sectors);

        return $planet;
    }

    private function incrementRemovedCompletelyRevealedPlanets(Planet $planet): void
    {
        if (!$planet->hasAllRevealedSectors()) {
            return;
        }

        $this->statusService->createOrIncrementChargeStatus(
            name: DaedalusStatusEnum::REMOVED_COMPLETELY_REVEALED_PLANETS,
            holder: $planet->getDaedalus(),
        );
    }
}
