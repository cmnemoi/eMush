<?php


namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;

interface GameFruitServiceInterface
{
    public function persist(GameFruit $gameFruit): GameFruit;

    public function findOneGamePlantByName(string $name, Daedalus $daedalus): ?GamePlant;

    public function createFruit(string $fruitName, Daedalus $daedalus): GameFruit;

    public function initGameFruits(Daedalus $daedalus);
}
