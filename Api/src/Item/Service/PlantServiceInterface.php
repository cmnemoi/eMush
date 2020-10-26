<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Plant;

interface PlantServiceInterface
{
    public function persist(Plant $plant): Plant;

    public function findOneGamePlantByName(string $name, Daedalus $daedalus): ?Plant;

    public function createPlant(string $fruitName, Daedalus $daedalus): Plant;

    public function initFruits(Daedalus $daedalus);
}
