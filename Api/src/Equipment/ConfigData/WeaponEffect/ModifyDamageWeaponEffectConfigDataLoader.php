<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData\WeaponEffect;

use Doctrine\ORM\EntityRepository;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyDamageWeaponEffectConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;

final class ModifyDamageWeaponEffectConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(ModifyDamageWeaponEffectConfig::class);

        foreach (EventConfigData::modifyDamageWeaponEffectConfigData() as $dto) {
            /** @var ?ModifyDamageWeaponEffectConfig $config */
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
