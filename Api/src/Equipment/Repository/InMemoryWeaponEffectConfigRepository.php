<?php

namespace Mush\Equipment\Repository;

use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Game\ConfigData\EventConfigData;

class InMemoryWeaponEffectConfigRepository implements WeaponEffectConfigRepositoryInterface
{
    public function findAllByWeaponEvent(WeaponEventConfig $weaponEvent): array
    {
        $weaponEffectConfigDtos = array_merge(
            EventConfigData::removeActionPointsWeaponEffectConfigData(),
            EventConfigData::modifyDamageWeaponEffectConfigData(),
            EventConfigData::oneShotWeaponEffectConfigData(),
            EventConfigData::inflictInjuryWeaponEffectConfigData(),
            EventConfigData::inflictRandomInjuryWeaponEffectConfigData(),
            EventConfigData::modifyDamageWeaponEffectConfigData(),
            EventConfigData::breakWeaponEffectConfigData(),
            EventConfigData::dropWeaponEffectConfigData(),
        );

        $weaponEventEffectKeys = $weaponEvent->getEffectKeys();

        $filter = array_filter($weaponEffectConfigDtos, static fn ($weaponEffectConfig) => \in_array($weaponEffectConfig->name, $weaponEventEffectKeys, true));
        $result = array_map(static fn ($dto) => $dto->toEntity(), $filter);

        return $result;
    }
}
