<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;

class ItemConfigFixtures extends EquipmentConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (EquipmentConfigData::$dataArray as $itemConfigData) {
            if ($itemConfigData['type'] !== 'item_config') {
                continue;
            }

            $itemConfig = new ItemConfig();

            $itemConfig->setIsStackable($itemConfigData['isStackable']);

            $this->setEquipmentConfigAttributes($itemConfig, $itemConfigData);
            $this->setEquipmentConfigActions($itemConfig, $itemConfigData, $manager);
            $this->setEquipmentConfigMechanics($itemConfig, $itemConfigData, $manager);
            $this->setEquipmentConfigStatusConfigs($itemConfig, $itemConfigData, $manager);

            $manager->persist($itemConfig);

            $this->addReference($itemConfig->getName(), $itemConfig);

            $gameConfig->addEquipmentConfig($itemConfig);
        }
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
}
