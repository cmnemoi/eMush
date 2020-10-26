<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\PlantStatusEnum;

class FruitService implements FruitServiceInterface
{
    private GameItemServiceInterface $itemService;
    private GameFruitServiceInterface $gameFruitService;

    /**
     * FruitService constructor.
     * @param GameItemServiceInterface $itemService
     * @param GameFruitServiceInterface $gameFruitService
     */
    public function __construct(GameItemServiceInterface $itemService, GameFruitServiceInterface $gameFruitService)
    {
        $this->itemService = $itemService;
        $this->gameFruitService = $gameFruitService;
    }

    public function createFruit(GameFruit $gameFruit): Fruit
    {
        $fruit = new Fruit();
        $fruit
            ->setName($gameFruit->getName())
            ->setGameFruit($gameFruit)
            ->setStatuses([])
        ;

        $this->itemService->persist($fruit);

        return $fruit;
    }

    public function createPlant(GamePlant $gamePlant): Plant
    {
        $plant = new Plant();
        $plant
            ->setName($gamePlant->getName())
            ->setGamePlant($gamePlant)
            ->setStatuses([PlantStatusEnum::YOUNG])
            ->setCharge(0);
        ;

        $this->itemService->persist($plant);

        return $plant;
    }

    public function createPlantFromName(string $gamePlantName, Daedalus $daedalus): Plant
    {
        $gamePlant = $this->gameFruitService->findOneGamePlantByName($gamePlantName, $daedalus);

        if (!$gamePlant) {
            $fruitName = GamePlantEnum::getGameFruit($gamePlantName);
            $gamePlant = $this->gameFruitService->createFruit($fruitName, $daedalus)->getGamePlant();
        }

        return $this->createPlant($gamePlant);
    }
}
