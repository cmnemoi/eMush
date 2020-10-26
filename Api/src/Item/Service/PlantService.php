<?php

namespace Mush\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\Plant;
use Mush\Item\Entity\Ration;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Repository\PlantRepository;

class PlantService implements PlantServiceInterface
{
    private RandomServiceInterface  $randomService;
    private EntityManagerInterface  $entityManager;
    private PlantRepository  $plantRepository;

    const MIN_MATURATION_TIME = 1;
    const MAX_MATURATION_TIME = 48;
    const FRUIT_CURES = [];
    const FRUIT_DISEASES = [];

    /**
     * GameFruitService constructor.
     * @param RandomServiceInterface $randomService
     * @param EntityManagerInterface $entityManager
     * @param PlantRepository $plantRepository
     */
    public function __construct(
        RandomServiceInterface $randomService,
        EntityManagerInterface $entityManager,
        PlantRepository $plantRepository
    ) {
        $this->randomService = $randomService;
        $this->entityManager = $entityManager;
        $this->plantRepository = $plantRepository;
    }

    public function persist(Plant $plant): Plant
    {
        $this->entityManager->persist($plant);
        $this->entityManager->flush();

        return $plant;
    }

    public function findOneGamePlantByName(string $name, Daedalus $daedalus): ?Plant
    {
        return $this->plantRepository->findOneByName($name, $daedalus);
    }

    /**
     * Create default game fruits
     */
    public function initFruits(Daedalus $daedalus)
    {
        $banana = new Fruit();
        $banana
            ->setName(GameFruitEnum::BANANA)
            ->setDaedalus($daedalus)
            ->setActionPoint(1)
            ->setHealthPoint(1)
            ->setMoralPoint(1)
            ->setDiseases([])
            ->setCures([])
        ;

        $bananaTree = new Plant();
        $bananaTree
            ->setMaturationTime(36)
            ->setOxygen(1)
            ->setName(GameFruitEnum::getGamePlant(GameFruitEnum::BANANA))
            ->setFruit($banana)
        ;

        $this->persist($bananaTree);
    }

    public function createPlant(string $fruitName, Daedalus $daedalus): Plant
    {
        $fruit = new Fruit();
        $fruit
            ->setName($fruitName)
            ->setDaedalus($daedalus)
            ->setActionPoint($this->randomService->random(0, 1))
            ->setHealthPoint(0)
            ->setMoralPoint($this->randomService->random(-1, 1))
            ->setSatiety(1)
            ->setCures(self::FRUIT_CURES)
            ->setDiseases(self::FRUIT_DISEASES)
        ;

        $plant = new Plant();
        $plant
            ->setName(GameFruitEnum::getGamePlant($fruitName))
            ->setFruit($fruit)
            ->setOxygen($this->randomService->random(0, 1))
            ->setMaturationTime($this->randomService->random(self::MIN_MATURATION_TIME, self::MAX_MATURATION_TIME))
        ;

        $this->persist($plant);

        return $plant;
    }
}
