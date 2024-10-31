<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData\WeaponEffect;

use Doctrine\ORM\EntityRepository;
use Mush\Equipment\Entity\Config\WeaponEffect\OneShotWeaponEffectConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;

final class OneShotWeaponEffectConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(OneShotWeaponEffectConfig::class);

        foreach (EventConfigData::oneShotWeaponEffectConfigData() as $dto) {
            /** @var ?OneShotWeaponEffectConfig $config */
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
