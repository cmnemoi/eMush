<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData;

use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\AbstractEventConfig;

final class WeaponEffectConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(AbstractEventConfig::class);

        foreach (EventConfigData::weaponEffectsConfigData() as $dto) {
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
