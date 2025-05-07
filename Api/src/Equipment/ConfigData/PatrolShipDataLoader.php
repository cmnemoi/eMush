<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Config\SpaceShipConfig;

class PatrolShipDataLoader extends EquipmentConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (EquipmentConfigData::$dataArray as $patrolShipData) {
            if ($patrolShipData['type'] !== 'patrol_ship') {
                continue;
            }

            $patrolShipConfig = $this->equipmentConfigRepository->findOneBy(['name' => $patrolShipData['name']]);

            if ($patrolShipConfig === null) {
                $patrolShipConfig = new SpaceShipConfig();
            } elseif (!$patrolShipConfig instanceof SpaceShipConfig) {
                $this->entityManager->remove($patrolShipConfig);
                $this->entityManager->flush();
                $patrolShipConfig = new SpaceShipConfig();
            }

            $this->setEquipmentConfigAttributes($patrolShipConfig, $patrolShipData);
            $this->setEquipmentConfigActions($patrolShipConfig, $patrolShipData);
            $this->setEquipmentConfigMechanics($patrolShipConfig, $patrolShipData);
            $this->setEquipmentConfigStatusConfigs($patrolShipConfig, $patrolShipData);

            $patrolShipConfig
                ->setCollectScrapNumber($patrolShipData['collectScrapNumber'])
                ->setCollectScrapPatrolShipDamage($patrolShipData['collectScrapPatrolShipDamage'])
                ->setCollectScrapPlayerDamage($patrolShipData['collectScrapPlayerDamage'])
                ->setFailedManoeuvreDaedalusDamage($patrolShipData['failedManoeuvreDaedalusDamage'])
                ->setFailedManoeuvrePatrolShipDamage($patrolShipData['failedManoeuvrePatrolShipDamage'])
                ->setFailedManoeuvrePlayerDamage($patrolShipData['failedManoeuvrePlayerDamage'])
                ->setNumberOfExplorationSteps($patrolShipData['numberOfExplorationSteps']);

            $this->entityManager->persist($patrolShipConfig);
        }
        $this->entityManager->flush();
    }
}
