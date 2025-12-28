<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Config\NpcConfig;

class NpcDataLoader extends EquipmentConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (EquipmentConfigData::$dataArray as $npcConfigData) {
            if ($npcConfigData['type'] !== 'npc_config') {
                continue;
            }

            $npcConfig = $this->equipmentConfigRepository->findOneBy(['name' => $npcConfigData['name']]);

            if ($npcConfig === null) {
                $npcConfig = new NpcConfig();
            } elseif (!$npcConfig instanceof NpcConfig) {
                $this->entityManager->remove($npcConfig);
                $this->entityManager->flush();
                $npcConfig = new NpcConfig();
            }

            $npcConfig->setIsStackable($npcConfigData['isStackable']);

            $this->setEquipmentConfigAttributes($npcConfig, $npcConfigData);
            $this->setEquipmentConfigActions($npcConfig, $npcConfigData);
            $this->setEquipmentConfigMechanics($npcConfig, $npcConfigData);
            $this->setEquipmentConfigStatusConfigs($npcConfig, $npcConfigData);
            $this->setNPCConfigAttributes($npcConfig, $npcConfigData);

            $this->entityManager->persist($npcConfig);
        }
        $this->entityManager->flush();
    }

    protected function setNPCConfigAttributes(NpcConfig $equipmentConfig, array $equipmentConfigData)
    {
        $equipmentConfig->setAiHandler($equipmentConfigData['AIHandler']);
    }
}
