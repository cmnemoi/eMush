<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ConsumableEffect;
use Mush\Equipment\Entity\Config\Mechanics\Plant;
use Mush\Equipment\Entity\Config\Mechanics\Ration;
use Mush\Equipment\Entity\Config\PlantEffect;

interface EquipmentEffectServiceInterface
{
    public function getConsumableEffect(Ration $ration, Daedalus $daedalus): ConsumableEffect;

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect;
}
