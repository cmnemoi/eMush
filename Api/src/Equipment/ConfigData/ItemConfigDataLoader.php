<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Config\ItemConfig;

class ItemConfigDataLoader extends EquipmentConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (EquipmentConfigData::$dataArray as $itemConfigData) {
            if ($itemConfigData['type'] !== 'item_config') {
                continue;
            }

            $itemConfig = $this->equipmentConfigRepository->findOneBy(['name' => $itemConfigData['name']]);

            if ($itemConfig === null) {
                $itemConfig = new ItemConfig();
            } elseif (!$itemConfig instanceof ItemConfig) {
                $this->entityManager->remove($itemConfig);
                $itemConfig = new ItemConfig();
            }

            $itemConfig->setIsStackable($itemConfigData['isStackable']);

            $this->setEquipmentConfigAttributes($itemConfig, $itemConfigData);
            $this->setEquipmentConfigActions($itemConfig, $itemConfigData);
            $this->setEquipmentConfigMechanics($itemConfig, $itemConfigData);
            $this->setEquipmentConfigStatusConfigs($itemConfig, $itemConfigData);

            $this->entityManager->persist($itemConfig);
        }
        $this->entityManager->flush();
    }
}
