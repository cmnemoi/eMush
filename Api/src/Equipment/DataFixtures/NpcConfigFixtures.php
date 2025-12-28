<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\NpcConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;

class NpcConfigFixtures extends EquipmentConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (EquipmentConfigData::$dataArray as $npcConfigData) {
            if ($npcConfigData['type'] !== 'npc_config') {
                continue;
            }

            $npcConfig = new NpcConfig();

            $npcConfig->setIsStackable($npcConfigData['isStackable']);

            $this->setEquipmentConfigAttributes($npcConfig, $npcConfigData);
            $this->setEquipmentConfigActions($npcConfig, $npcConfigData, $manager);
            $this->setEquipmentConfigMechanics($npcConfig, $npcConfigData, $manager);
            $this->setEquipmentConfigStatusConfigs($npcConfig, $npcConfigData, $manager);
            $this->setNPCConfigAttributes($npcConfig, $npcConfigData);

            $manager->persist($npcConfig);

            $gameConfig->addEquipmentConfig($npcConfig);
        }
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
        ];
    }

    protected function setNPCConfigAttributes(NpcConfig $equipmentConfig, array $equipmentConfigData)
    {
        $equipmentConfig->setAiHandler($equipmentConfigData['AIHandler']);
    }
}
