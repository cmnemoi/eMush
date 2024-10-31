<?php

namespace Mush\Equipment\Repository;

use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Game\ConfigData\EventConfigData;

class InMemoryWeaponEventConfigRepository implements WeaponEventConfigRepositoryInterface
{
    public function findOneByKey(string $eventKey): WeaponEventConfig
    {
        return EventConfigData::getWeaponEventConfigByName($eventKey)->toEntity();
    }
}
