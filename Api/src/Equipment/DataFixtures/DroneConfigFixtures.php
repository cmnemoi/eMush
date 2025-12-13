<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\DroneConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;

class DroneConfigFixtures extends EquipmentConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (EquipmentConfigData::$dataArray as $droneConfigData) {
            if ($droneConfigData['type'] !== 'drone_config') {
                continue;
            }

            $droneConfig = new DroneConfig();

            $droneConfig->setIsStackable($droneConfigData['isStackable']);

            $this->setEquipmentConfigAttributes($droneConfig, $droneConfigData);
            $this->setEquipmentConfigActions($droneConfig, $droneConfigData, $manager);
            $this->setEquipmentConfigMechanics($droneConfig, $droneConfigData, $manager);
            $this->setEquipmentConfigStatusConfigs($droneConfig, $droneConfigData, $manager);

            $manager->persist($droneConfig);

            $gameConfig->addEquipmentConfig($droneConfig);
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
}
