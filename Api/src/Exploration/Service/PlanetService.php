<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\SpaceCoordinates;
use Mush\Exploration\Repository\PlanetRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

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

        $availableCoordinates = $this->getAvailaibleCoordinatesForPlanet($planet);

        // get a random coordinates pair from the available ones and set it to the planet
        $drawnCoordinates = $this->randomService->getRandomElement($availableCoordinates);
        $planet->setCoordinates($drawnCoordinates);

        $planet = $this->generatePlanetSectors($planet);

        $this->persist([$planet]);

        return $planet;
    }

    public function findById(int $id): ?Planet
    {
        return $this->planetRepository->find($id);
    }

    public function findPlanetSectorById(int $id): ?PlanetSector
    {
        return $this->entityManager->find(PlanetSector::class, $id);
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    private function getAvailaibleCoordinatesForPlanet(Planet $planet): array
    {
        $availableCoordinates = SpaceCoordinates::getAll();

        $existingPlanets = $this->planetRepository->findAllByDaedalus($planet->getDaedalus());
        foreach ($existingPlanets as $existingPlanet) {
            foreach ($availableCoordinates as $coordinates) {
                if ($existingPlanet->getCoordinates()->equals($coordinates)) {
                    $availableCoordinates->removeElement($coordinates);
                }
            }
        }

        return $availableCoordinates->toArray();
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
        $size = 2 + $this->randomService->random(0, 6) * 2;

        if ($dadalus->isInHardMode()) {
            $size = 4 + $this->randomService->random(0, 7) * 2;
        } elseif ($dadalus->isInVeryHardMode()) {
            $size = 6 + $this->randomService->random(0, 8) * 2;
        }

        return $size;
    }

    private function generatePlanetSectors(Planet $planet): Planet
    {
        /** @var ArrayCollection<int, PlanetSector> $sectors */
        $sectors = new ArrayCollection();

        $gameConfig = $planet->getDaedalus()->getGameConfig();
        $allSectorConfigs = $gameConfig->getPlanetSectorConfigs();
        $total = $this->getGameConfigTotalWeightAtPlanetGeneration($gameConfig);

        // Generate a sector for each available slot on the planet
        for ($i = 0; $i < $planet->getSize(); ++$i) {
            $random = $this->randomService->random(0, $total);
            $sum = 0;

            // Iterate over all possible sectors
            /** @var PlanetSectorConfig $sectorConfig */
            foreach ($allSectorConfigs as $sectorConfig) {
                // Get the maximum number of times this sector can appear on a planet
                $maxPerPlanet = $sectorConfig->getMaxPerPlanet();

                // Add the weight of this sector  to the running sum
                $sum += $sectorConfig->getWeightAtPlanetGeneration();

                // If the running sum is greater than the random number, add the sector to the planet
                if ($sum > $random) {
                    $sectors->add(new PlanetSector($sectorConfig, $planet));

                    // Decrement the maximum number of times this sector can appear on a planet
                    --$maxPerPlanet;

                    // If the maximum number of times this sector can appear on a planet has been reached, remove it from the list of available sectors
                    if ($maxPerPlanet === 0) {
                        $allSectorConfigs->removeElement($sectorConfig);

                        // Subtract the weight of this sector configuration from the total weight of all the sector since it can no longer be generated
                        $total -= $sectorConfig->getWeightAtPlanetGeneration();
                    }

                    // Break out of the loop since we've generated a sector for this slot on the planet
                    break;
                }
            }
        }

        $planet->setSectors($sectors);

        return $planet;
    }

    private function getGameConfigTotalWeightAtPlanetGeneration(GameConfig $gameConfig): int
    {
        $total = 0;
        foreach ($gameConfig->getPlanetSectorConfigs() as $sectorConfig) {
            $total += $sectorConfig->getWeightAtPlanetGeneration();
        }

        return $total;
    }
}
