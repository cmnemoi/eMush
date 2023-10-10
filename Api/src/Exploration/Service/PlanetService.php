<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Enum\PlanetSyllablesEnum;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

final class PlanetService implements PlanetServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;

    public function __construct(EntityManagerInterface $entityManager, RandomServiceInterface $randomService)
    {
        $this->entityManager = $entityManager;
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

    private function findOneBy(array $criteria): ?Planet
    {
        return $this->entityManager->getRepository(Planet::class)->findOneBy($criteria);
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}
