<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData\WeaponEffect;

use Doctrine\ORM\EntityRepository;
use Mush\Equipment\Entity\Config\WeaponEffect\InflictInjuryWeaponEffectConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;

final class InflictInjuryWeaponEffectConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(InflictInjuryWeaponEffectConfig::class);

        foreach (EventConfigData::inflictInjuryWeaponEffectConfigData() as $dto) {
            /** @var ?InflictInjuryWeaponEffectConfig $config */
            $config = $repository->findOneBy(['name' => $dto->name]);

            if ($config === null) {
                $config = $dto->toEntity();
            } else {
                $config->updateFromDto($dto);
            }

            $this->entityManager->persist($config);
        }
        $this->entityManager->flush();
    }
}
