<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Plant;

interface FruitServiceInterface
{
    public function createFruit(GameFruit $gameFruit): Fruit;

    public function createPlant(GamePlant $gamePlant): Plant;

    public function createPlantFromName(string $gamePlantName, Daedalus $daedalus): Plant;
}
