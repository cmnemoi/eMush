<?php


namespace Mush\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Repository\GameFruitRepository;
use Mush\Item\Repository\GamePlantRepository;

class GameFruitService implements GameFruitServiceInterface
{
    private RandomServiceInterface  $randomService;
    private EntityManagerInterface  $entityManager;
    private GameFruitRepository  $gameFruitRepository;
    private GamePlantRepository  $gamePlantRepository;

    const MIN_MATURATION_TIME = 1;
    const MAX_MATURATION_TIME = 48;
    const FRUIT_CURES = [];
    const FRUIT_DISEASES = [];

    /**
     * GameFruitService constructor.
     * @param RandomServiceInterface $randomService
     * @param EntityManagerInterface $entityManager
     * @param GameFruitRepository $gameFruitRepository
     * @param GamePlantRepository $gamePlantRepository
     */
    public function __construct(
        RandomServiceInterface $randomService,
        EntityManagerInterface $entityManager,
        GameFruitRepository $gameFruitRepository,
        GamePlantRepository $gamePlantRepository
    ) {
        $this->randomService = $randomService;
        $this->entityManager = $entityManager;
        $this->gameFruitRepository = $gameFruitRepository;
        $this->gamePlantRepository = $gamePlantRepository;
    }

    public function persist(GameFruit $gameFruit): GameFruit
    {
        $this->entityManager->persist($gameFruit);
        $this->entityManager->flush();

        return $gameFruit;
    }

    public function findOneGamePlantByName(string $name, Daedalus $daedalus): ?GamePlant
    {
        return $this->gamePlantRepository->findOneByName($name, $daedalus);
    }

    /**
     * Create default game fruits
     */
    public function initGameFruits(Daedalus $daedalus)
    {
        $bananaTree = new GamePlant();
        $bananaTree
            ->setMaturationTime(36)
            ->setOxygen(1)
            ->setName(GameFruitEnum::getGamePlant(GameFruitEnum::BANANA))
        ;

        $banana = new GameFruit();
        $banana
            ->setName(GameFruitEnum::BANANA)
            ->setDaedalus($daedalus)
            ->setGamePlant($bananaTree)
            ->setActionPoint(1)
            ->setHealthPoint(1)
            ->setMoralPoint(1)
            ->setDiseases([])
            ->setCures([])
        ;

        $this->persist($banana);
    }

    public function createFruit(string $fruitName, Daedalus $daedalus): GameFruit
    {
        $plant = new GamePlant();
        $plant
            ->setName(GameFruitEnum::getGamePlant($fruitName))
            ->setOxygen($this->randomService->random(0, 1))
            ->setMaturationTime($this->randomService->random(self::MIN_MATURATION_TIME, self::MAX_MATURATION_TIME))
        ;

        $fruit = new GameFruit();
        $fruit
            ->setName($fruitName)
            ->setDaedalus($daedalus)
            ->setGamePlant($plant)
            ->setActionPoint($this->randomService->random(0, 1))
            ->setHealthPoint(0)
            ->setMoralPoint($this->randomService->random(-1, 1))
            ->setSatiety(1)
            ->setCures(self::FRUIT_CURES)
            ->setDiseases(self::FRUIT_DISEASES)
        ;

        $this->persist($fruit);

        return $fruit;
    }
}
