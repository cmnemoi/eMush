<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\PlantEffect;

interface EquipmentEffectServiceInterface
{
    public function getConsumableEffect(Ration $ration, Daedalus $daedalus): ConsumableEffect;

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect;
}
