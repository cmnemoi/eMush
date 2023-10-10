<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSyllablesEnum;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Exploration\Repository\PlanetSectorConfigRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

final class PlanetService implements PlanetServiceInterface
{
    private EntityManagerInterface $entityManager;
    private PlanetSectorConfigRepository $planetSectorConfigRepository;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PlanetSectorConfigRepository $planetSectorConfigRepository,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->planetSectorConfigRepository = $planetSectorConfigRepository;
        $this->randomService = $randomService;
    }

    public function createPlanet(Player $player): Planet
    {
        $daedalus = $player->getDaedalus();

        $planet = new Planet($player);
        $planet
            ->setName($this->getPlanetName())
            ->setSize($this->getPlanetSize($daedalus))
        ;

        // create unique planet
        do {
            $planet->setOrientation($this->randomService->getRandomElement(SpaceOrientationEnum::getAll()));
            $planet->setDistance($this->randomService->random(2, 9));
        } while (
            $this->findOneBy(['orientation' => $planet->getOrientation(), 'distance' => $planet->getDistance(), 'daedalus' => $daedalus]) !== null
        );

        $this->persist([$planet]);

        $planet = $this->generatePlanetSectors($planet);

        $this->persist([$planet]);

        return $planet;
    }

    private function getPlanetName(): string
    {
        $planetName = $this->randomService->getRandomElement(PlanetSyllablesEnum::$first);

        if ($this->randomService->isSuccessful(10)) {
            $planetName .= $this->randomService->getRandomElement(PlanetSyllablesEnum::$second);
        }

        if ($this->randomService->isSuccessful(40)) {
            $planetName .= $this->randomService->getRandomElement(PlanetSyllablesEnum::$second);
        }

        $planetName .= $this->randomService->getRandomElement(PlanetSyllablesEnum::$third);

        if ($this->randomService->isSuccessful(3)) {
            $planetName .= ' ' . $this->randomService->getRandomElement(PlanetSyllablesEnum::$fourth);
        } elseif ($this->randomService->isSuccessful(30)) {
            $planetName = $this->randomService->getRandomElement(PlanetSyllablesEnum::$fourth) . ' ' . $planetName;
        }

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

        $allSectorConfigs = $this->findAllPlanetSectorConfigs();
        $total = $this->findTotalWeightsAtPlanetGeneration();

        for ($i = 0; $i < $planet->getSize(); ++$i) {
            $random = $this->randomService->random(0, $total);
            $sum = 0;

            /** @var PlanetSectorConfig $sectorConfig */
            foreach ($allSectorConfigs as $sectorConfig) {
                $maxPerPlanet = $sectorConfig->getMaxPerPlanet();
                $sum += $sectorConfig->getWeightAtPlanetGeneration();

                if ($sum > $random) {
                    $sectors->add(new PlanetSector($sectorConfig, $planet));
                    --$maxPerPlanet;
                    if ($maxPerPlanet === 0) {
                        $allSectorConfigs->removeElement($sectorConfig);
                        $total -= $sectorConfig->getWeightAtPlanetGeneration();
                    }
                    break;
                }
            }
        }

        $this->persist($sectors->toArray());
        $planet->setSectors($sectors);

        return $planet;
    }

    private function findOneBy(array $criteria): ?Planet
    {
        return $this->entityManager->getRepository(Planet::class)->findOneBy($criteria);
    }

    private function findAllPlanetSectorConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->planetSectorConfigRepository->findAll());
    }

    private function findTotalWeightsAtPlanetGeneration(): int
    {
        return $this->planetSectorConfigRepository->findTotalWeightsAtPlanetGeneration();
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}
