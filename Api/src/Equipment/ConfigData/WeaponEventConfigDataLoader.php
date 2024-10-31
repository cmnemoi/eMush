<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData;

use Doctrine\ORM\EntityRepository;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;

final class WeaponEventConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(WeaponEventConfig::class);

        foreach (EventConfigData::weaponEventConfigData() as $dto) {
            /** @var ?WeaponEventConfig $weaponEventConfig */
            $weaponEventConfig = $repository->findOneBy(['name' => $dto->name]);

            if ($weaponEventConfig === null) {
                $weaponEventConfig = $dto->toEntity();
            } else {
                $weaponEventConfig->updateFromDto($dto);
            }

            $this->entityManager->persist($weaponEventConfig);
        }
        $this->entityManager->flush();
    }
}
