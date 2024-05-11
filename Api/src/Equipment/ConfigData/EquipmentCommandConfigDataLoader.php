<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Config\ReplaceEquipmentConfig;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Game\ConfigData\ConfigDataLoader;

final class EquipmentCommandConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (SpawnEquipmentConfigData::getAll() as $spawnEquipmentConfigData) {
            /** @var null|SpawnEquipmentConfig $spawnEquipmentConfig */
            $spawnEquipmentConfig = $this->entityManager->getRepository(SpawnEquipmentConfig::class)->findOneBy([
                'name' => $spawnEquipmentConfigData->name,
            ]);

            if ($spawnEquipmentConfig) {
                $spawnEquipmentConfig->updateFromDto($spawnEquipmentConfigData);
            } else {
                $spawnEquipmentConfig = $spawnEquipmentConfigData->toEntity();
            }
            $this->entityManager->persist($spawnEquipmentConfig);
        }

        foreach (ReplaceEquipmentConfigData::getAll() as $replaceEquipmentConfigData) {
            /** @var null|ReplaceEquipmentConfig $replaceEquipmentConfig */
            $replaceEquipmentConfig = $this->entityManager->getRepository(ReplaceEquipmentConfig::class)->findOneBy([
                'name' => $replaceEquipmentConfigData->name,
            ]);

            if ($replaceEquipmentConfig) {
                $replaceEquipmentConfig->updateFromDto($replaceEquipmentConfigData);
            } else {
                $replaceEquipmentConfig = $replaceEquipmentConfigData->toEntity();
            }
            $this->entityManager->persist($replaceEquipmentConfig);
        }

        $this->entityManager->flush();
    }
}
