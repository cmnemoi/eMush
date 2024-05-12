<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Config\DroneConfig;

final class DroneDataLoader extends EquipmentConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (EquipmentConfigData::$dataArray as $droneConfigData) {
            if ($droneConfigData['type'] !== 'drone_config') {
                continue;
            }

            $droneConfig = $this->equipmentConfigRepository->findOneBy(['name' => $droneConfigData['name']]);

            if ($droneConfig === null) {
                $droneConfig = new DroneConfig();
            } elseif (!$droneConfig instanceof DroneConfig) {
                $this->entityManager->remove($droneConfig);
                $this->entityManager->flush();
                $droneConfig = new DroneConfig();
            }

            $droneConfig->setIsStackable($droneConfigData['isStackable']);

            $this->setEquipmentConfigAttributes($droneConfig, $droneConfigData);
            $this->setEquipmentConfigActions($droneConfig, $droneConfigData);
            $this->setEquipmentConfigMechanics($droneConfig, $droneConfigData);
            $this->setEquipmentConfigStatusConfigs($droneConfig, $droneConfigData);

            $this->entityManager->persist($droneConfig);
        }
        $this->entityManager->flush();
    }
}
