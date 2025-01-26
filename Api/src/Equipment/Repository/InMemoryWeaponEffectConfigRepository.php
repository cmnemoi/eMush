<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Game\ConfigData\EventConfigData;

final class InMemoryWeaponEffectConfigRepository implements WeaponEffectConfigRepositoryInterface
{
    public function findAllByWeaponEvent(WeaponEventConfig $weaponEvent): array
    {
        $weaponEffectConfigDtos = EventConfigData::weaponEffectsConfigData();

        $weaponEventEffectKeys = $weaponEvent->getEffectKeys();

        $filter = array_filter($weaponEffectConfigDtos, static fn ($weaponEffectConfig) => \in_array($weaponEffectConfig->name, $weaponEventEffectKeys, true));
        $result = array_map(static fn ($dto) => $dto->toEntity(), $filter);

        return $result;
    }
}
