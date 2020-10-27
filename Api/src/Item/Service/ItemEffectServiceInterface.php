<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\ConsumableEffect;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\PlantEffect;

interface ItemEffectServiceInterface
{
    public function getConsumableEffect(Ration $ration, Daedalus $daedalus): ConsumableEffect;

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect;
}