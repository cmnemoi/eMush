<?php


namespace Mush\Item\Service;


use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Repository\GameFruitRepository;

class GameFruitService implements GameFruitServiceInterface
{
    private RandomServiceInterface  $randomService;
    private EntityManagerInterface  $entityManager;
    private GameFruitRepository  $repository;

    const MIN_MATURATION_TIME = 8;
    const MAX_MATURATION_TIME = 36;
    const FRUIT_CURES = [];
    const FRUIT_DISEASES = [];

    /**
     * GameFruitService constructor.
     * @param RandomServiceInterface $randomService
     * @param EntityManagerInterface $entityManager
     * @param GameFruitRepository $repository
     */
    public function __construct(RandomServiceInterface $randomService, EntityManagerInterface $entityManager, GameFruitRepository $repository)
    {
        $this->randomService = $randomService;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(GameFruit $gameFruit): GameFruit
    {
        $this->entityManager->persist($gameFruit);
        $this->entityManager->flush();

        return $gameFruit;
    }

    /**
     * Create bananas with hardcoded values
     * @param Daedalus $daedalus
     * @return GameFruit
     */
    public function createBanana(Daedalus $daedalus): GameFruit
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

        return $this->persist($banana);
    }

    public function createFruit(Daedalus $daedalus): GameFruit
    {
        $fruitName = $this->getNewFruitName($daedalus);

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

    /**
     * Find a fruit name that have not already been created for this Daedalus
     * @param Daedalus $daedalus
     * @return string
     */
    private function getNewFruitName(Daedalus $daedalus): string
    {
        $fruits = GameFruitEnum::getAll();
        $daedalusFruits = $this->repository->findBy(['daedalus' => $daedalus]);

        $fruitsAvailable = array_filter($fruits,
            fn(string $currentFruitName) => (!current(array_filter($daedalusFruits, fn(GameFruit $element) => $element->getName() === $currentFruitName)))
        );

        return $fruitsAvailable[$this->randomService->random(0, count($fruitsAvailable) - 1)];
    }
}